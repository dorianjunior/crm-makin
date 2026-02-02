<?php

use App\Models\CRM\Company;
use App\Models\CRM\Lead;
use App\Models\Social\WhatsAppAccount;
use App\Models\Social\WhatsAppConversation;
use App\Models\Social\WhatsAppMessage;
use App\Services\Social\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->account = WhatsAppAccount::factory()->create([
        'company_id' => $this->company->id,
        'phone_number_id' => '123456789',
        'access_token' => encrypt('test_access_token'),
    ]);
    $this->service = new WhatsAppService();
});

describe('WhatsApp Service - Sending Messages', function () {
    test('can send text message', function () {
        Http::fake([
            'graph.facebook.com/*/messages' => Http::response([
                'messages' => [['id' => 'wamid.TEST123']],
            ], 200),
        ]);

        $result = $this->service->sendMessage(
            $this->account->id,
            '+5511988888888',
            'Test message'
        );

        expect($result)->toBeArray()
            ->and($result['message_id'])->toBe('wamid.TEST123')
            ->and($result['conversation'])->toBeInstanceOf(WhatsAppConversation::class);

        Http::assertSent(function ($request) {
            return $request->url() === "https://graph.facebook.com/v18.0/{$this->account->phone_number_id}/messages"
                && $request['messaging_product'] === 'whatsapp'
                && $request['to'] === '5511988888888'
                && $request['type'] === 'text'
                && $request['text']['body'] === 'Test message';
        });
    });

    test('can send media message', function () {
        Http::fake([
            'graph.facebook.com/*/messages' => Http::response([
                'messages' => [['id' => 'wamid.MEDIA123']],
            ], 200),
        ]);

        $result = $this->service->sendMediaMessage(
            $this->account->id,
            '+5511988888888',
            'https://example.com/image.jpg',
            'image',
            'Check this out'
        );

        expect($result)->toBeArray()
            ->and($result['message_id'])->toBe('wamid.MEDIA123');

        Http::assertSent(function ($request) {
            return $request['messaging_product'] === 'whatsapp'
                && $request['type'] === 'image'
                && $request['image']['link'] === 'https://example.com/image.jpg'
                && $request['image']['caption'] === 'Check this out';
        });
    });

    test('throws exception for invalid account', function () {
        expect(fn () => $this->service->sendMessage(99999, '+5511988888888', 'Test'))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    test('throws exception for inactive account', function () {
        $this->account->update(['is_active' => false]);

        expect(fn () => $this->service->sendMessage($this->account->id, '+5511988888888', 'Test'))
            ->toThrow(\Exception::class, 'WhatsApp account is not active');
    });
});

describe('WhatsApp Service - Phone Normalization', function () {
    test('normalizes brazilian phone with country code', function () {
        $normalized = invade($this->service)->normalizePhone('+5511988888888');
        expect($normalized)->toBe('5511988888888');
    });

    test('adds country code to phone without it', function () {
        $normalized = invade($this->service)->normalizePhone('11988888888');
        expect($normalized)->toBe('5511988888888');
    });

    test('removes formatting from phone', function () {
        $normalized = invade($this->service)->normalizePhone('+55 (11) 98888-8888');
        expect($normalized)->toBe('5511988888888');
    });

    test('handles phone with 011 prefix', function () {
        $normalized = invade($this->service)->normalizePhone('011988888888');
        expect($normalized)->toBe('5511988888888');
    });
});

describe('WhatsApp Service - Conversation Management', function () {
    test('creates new conversation if not exists', function () {
        $conversation = invade($this->service)->getOrCreateConversation(
            $this->account->id,
            '+5511988888888',
            'João Silva',
            'https://example.com/avatar.jpg'
        );

        expect($conversation)->toBeInstanceOf(WhatsAppConversation::class)
            ->and($conversation->conversation_id)->toBe('5511988888888')
            ->and($conversation->contact_name)->toBe('João Silva')
            ->and($conversation->contact_phone)->toBe('+5511988888888');
    });

    test('returns existing conversation', function () {
        $existing = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
            'conversation_id' => '5511988888888',
        ]);

        $conversation = invade($this->service)->getOrCreateConversation(
            $this->account->id,
            '+5511988888888',
            'João Silva',
            null
        );

        expect($conversation->id)->toBe($existing->id);
    });

    test('links conversation to lead by phone', function () {
        $lead = Lead::factory()->create([
            'company_id' => $this->company->id,
            'phone' => '+5511988888888',
        ]);

        $conversation = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
            'contact_phone' => '+5511988888888',
            'lead_id' => null,
        ]);

        invade($this->service)->linkConversationToLead($conversation);

        expect($conversation->fresh()->lead_id)->toBe($lead->id);
    });

    test('links conversation by last 10 digits', function () {
        $lead = Lead::factory()->create([
            'company_id' => $this->company->id,
            'phone' => '11988888888', // Without country code
        ]);

        $conversation = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
            'contact_phone' => '+5511988888888',
            'lead_id' => null,
        ]);

        invade($this->service)->linkConversationToLead($conversation);

        expect($conversation->fresh()->lead_id)->toBe($lead->id);
    });
});

describe('WhatsApp Service - Message Status', function () {
    test('updates message status', function () {
        $conversation = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
        ]);

        $message = WhatsAppMessage::factory()->create([
            'whatsapp_conversation_id' => $conversation->id,
            'message_id' => 'wamid.TEST123',
            'status' => 'sent',
        ]);

        $this->service->updateMessageStatus('wamid.TEST123', 'delivered');

        expect($message->fresh()->status)->toBe('delivered')
            ->and($message->fresh()->delivered_at)->not->toBeNull();
    });

    test('updates message to read status', function () {
        $conversation = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
        ]);

        $message = WhatsAppMessage::factory()->create([
            'whatsapp_conversation_id' => $conversation->id,
            'message_id' => 'wamid.TEST123',
            'status' => 'delivered',
        ]);

        $this->service->updateMessageStatus('wamid.TEST123', 'read');

        expect($message->fresh()->status)->toBe('read')
            ->and($message->fresh()->read_at)->not->toBeNull();
    });

    test('updates message to failed status with error', function () {
        $conversation = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
        ]);

        $message = WhatsAppMessage::factory()->create([
            'whatsapp_conversation_id' => $conversation->id,
            'message_id' => 'wamid.TEST123',
            'status' => 'pending',
        ]);

        $this->service->updateMessageStatus('wamid.TEST123', 'failed', 'Connection timeout');

        expect($message->fresh()->status)->toBe('failed')
            ->and($message->fresh()->error_message)->toBe('Connection timeout');
    });
});

describe('WhatsApp Service - Media Download', function () {
    test('downloads and stores media file', function () {
        Storage::fake('public');

        Http::fake([
            'graph.facebook.com/v18.0/MEDIA123' => Http::response([
                'url' => 'https://media.example.com/file.jpg',
                'mime_type' => 'image/jpeg',
            ], 200),
            'media.example.com/*' => Http::response('fake image content', 200),
        ]);

        $filePath = invade($this->service)->downloadMedia($this->account->id, 'MEDIA123', 'image/jpeg');

        expect($filePath)->toContain('whatsapp/media/')
            ->and(Storage::disk('public')->exists($filePath))->toBeTrue();
    });

    test('returns null if media download fails', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([], 404),
        ]);

        $filePath = invade($this->service)->downloadMedia($this->account->id, 'INVALID_MEDIA_ID', 'image/jpeg');

        expect($filePath)->toBeNull();
    });

    test('gets correct file extension from mime type', function () {
        $service = invade($this->service);

        expect($service->getExtensionFromMimeType('image/jpeg'))->toBe('jpg')
            ->and($service->getExtensionFromMimeType('image/png'))->toBe('png')
            ->and($service->getExtensionFromMimeType('video/mp4'))->toBe('mp4')
            ->and($service->getExtensionFromMimeType('audio/mpeg'))->toBe('mp3')
            ->and($service->getExtensionFromMimeType('application/pdf'))->toBe('pdf')
            ->and($service->getExtensionFromMimeType('application/octet-stream'))->toBe('bin');
    });
});

describe('WhatsApp Service - Fetching Data', function () {
    test('fetches messages from account', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messages' => [
                    [
                        'id' => 'wamid.MSG1',
                        'from' => '5511988888888',
                        'timestamp' => '1642258200',
                        'type' => 'text',
                        'text' => ['body' => 'Hello'],
                    ],
                ],
            ], 200),
        ]);

        $messages = $this->service->fetchMessages($this->account->id, 10);

        expect($messages)->toBeArray()
            ->and($messages)->toHaveCount(1)
            ->and($messages[0]['id'])->toBe('wamid.MSG1');
    });

    test('fetches conversations from account', function () {
        WhatsAppConversation::factory()->count(3)->create([
            'whatsapp_account_id' => $this->account->id,
        ]);

        WhatsAppMessage::factory()->create([
            'whatsapp_conversation_id' => WhatsAppConversation::first()->id,
            'content' => 'Last message',
        ]);

        $conversations = $this->service->getConversations($this->account->id, 10);

        expect($conversations)->toHaveCount(3)
            ->and($conversations->first()->last_message)->not->toBeNull();
    });
});

describe('WhatsApp Service - Connection Status', function () {
    test('checks if account is connected', function () {
        $connected = $this->service->isConnected($this->account->id);
        expect($connected)->toBeTrue();
    });

    test('returns false for inactive account', function () {
        $this->account->update(['is_active' => false]);

        $connected = $this->service->isConnected($this->account->id);
        expect($connected)->toBeFalse();
    });

    test('disconnects account', function () {
        $result = $this->service->disconnect($this->account->id);

        expect($result)->toBeTrue()
            ->and($this->account->fresh()->is_active)->toBeFalse();
    });
});

describe('WhatsApp Service - Mark as Read', function () {
    test('marks message as read in whatsapp', function () {
        Http::fake([
            'graph.facebook.com/*/messages' => Http::response(['success' => true], 200),
        ]);

        $result = $this->service->markAsRead($this->account->id, 'wamid.TEST123');

        expect($result)->toBeTrue();

        Http::assertSent(function ($request) {
            return $request['messaging_product'] === 'whatsapp'
                && $request['status'] === 'read'
                && $request['message_id'] === 'wamid.TEST123';
        });
    });

    test('handles mark as read failure gracefully', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([], 400),
        ]);

        $result = $this->service->markAsRead($this->account->id, 'wamid.INVALID');

        expect($result)->toBeFalse();
    });
});
