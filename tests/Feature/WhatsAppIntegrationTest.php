<?php

use App\Models\Admin\Role;
use App\Models\Admin\User;
use App\Models\CRM\Company;
use App\Models\CRM\Lead;
use App\Models\Social\WhatsAppAccount;
use App\Models\Social\WhatsAppConversation;
use App\Models\Social\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->role = Role::factory()->create(['company_id' => $this->company->id]);
    $this->user = User::factory()->create([
        'company_id' => $this->company->id,
        'role_id' => $this->role->id,
    ]);
    Sanctum::actingAs($this->user);

    $this->account = WhatsAppAccount::factory()->create([
        'company_id' => $this->company->id,
        'phone_number_id' => '123456789',
        'is_active' => true,
    ]);
});

describe('WhatsApp Account Management', function () {
    test('can list whatsapp accounts', function () {
        $response = $this->getJson('/api/social/whatsapp/accounts');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'phone_number',
                        'display_name',
                        'account_type',
                        'quality_rating',
                        'is_active',
                    ],
                ],
            ]);
    });

    test('can register new whatsapp account', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);

        $data = [
            'phone_number_id' => '987654321',
            'business_account_id' => '111222333',
            'phone_number' => '+5511999999999',
            'display_name' => 'Test Account',
            'access_token' => 'test_token_123',
            'verify_token' => 'verify_123',
            'account_type' => 'OFFICIAL',
            'quality_rating' => 'GREEN',
        ];

        $response = $this->postJson('/api/social/whatsapp/accounts', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'phone_number',
                    'display_name',
                    'is_active',
                ],
            ]);

        $this->assertDatabaseHas('whatsapp_accounts', [
            'company_id' => $this->company->id,
            'phone_number_id' => '987654321',
            'phone_number' => '+5511999999999',
        ]);
    });

    test('cannot access other company accounts', function () {
        $otherCompany = Company::factory()->create();
        $otherAccount = WhatsAppAccount::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $response = $this->getJson("/api/social/whatsapp/accounts/{$otherAccount->id}/conversations");

        $response->assertNotFound();
    });

    test('can disconnect account', function () {
        $response = $this->deleteJson("/api/social/whatsapp/accounts/{$this->account->id}/disconnect");

        $response->assertOk();

        $this->assertDatabaseHas('whatsapp_accounts', [
            'id' => $this->account->id,
            'is_active' => false,
        ]);
    });
});

describe('WhatsApp Conversations', function () {
    beforeEach(function () {
        $this->conversation = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
            'contact_phone' => '+5511988888888',
            'unread_count' => 3,
        ]);
    });

    test('can list conversations', function () {
        $response = $this->getJson("/api/social/whatsapp/accounts/{$this->account->id}/conversations");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'conversation_id',
                        'contact_name',
                        'contact_phone',
                        'unread_count',
                        'last_message_at',
                        'status',
                    ],
                ],
            ]);
    });

    test('can filter conversations by status', function () {
        WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
            'status' => 'archived',
        ]);

        $response = $this->getJson("/api/social/whatsapp/accounts/{$this->account->id}/conversations?status=active");

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
    });

    test('can filter unread conversations', function () {
        WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
            'unread_count' => 0,
        ]);

        $response = $this->getJson("/api/social/whatsapp/accounts/{$this->account->id}/conversations?unread_only=true");

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.unread_count'))->toBeGreaterThan(0);
    });

    test('can mark conversation as read', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->postJson("/api/social/whatsapp/conversations/{$this->conversation->id}/mark-read");

        $response->assertOk();

        $this->assertDatabaseHas('whatsapp_conversations', [
            'id' => $this->conversation->id,
            'unread_count' => 0,
        ]);
    });
});

describe('WhatsApp Messages', function () {
    beforeEach(function () {
        $this->conversation = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
        ]);

        WhatsAppMessage::factory()->count(5)->create([
            'whatsapp_conversation_id' => $this->conversation->id,
        ]);
    });

    test('can list messages from conversation', function () {
        $response = $this->getJson("/api/social/whatsapp/conversations/{$this->conversation->id}/messages");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'message_id',
                        'direction',
                        'type',
                        'content',
                        'status',
                        'from_phone',
                        'to_phone',
                        'sent_at',
                    ],
                ],
            ]);

        expect($response->json('data'))->toHaveCount(5);
    });

    test('can limit number of messages', function () {
        $response = $this->getJson("/api/social/whatsapp/conversations/{$this->conversation->id}/messages?limit=2");

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(2);
    });
});

describe('Sending WhatsApp Messages', function () {
    beforeEach(function () {
        Queue::fake();
    });

    test('can send text message', function () {
        Http::fake([
            'graph.facebook.com/*/messages' => Http::response([
                'messages' => [
                    ['id' => 'wamid.TEST123'],
                ],
            ], 200),
        ]);

        $data = [
            'to' => '+5511988888888',
            'message' => 'Olá, teste de mensagem',
        ];

        $response = $this->postJson("/api/social/whatsapp/accounts/{$this->account->id}/send", $data);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message_id',
                'conversation',
            ]);

        $this->assertDatabaseHas('whatsapp_messages', [
            'content' => 'Olá, teste de mensagem',
            'direction' => 'outbound',
            'type' => 'text',
            'status' => 'sent',
        ]);
    });

    test('can send media message', function () {
        Http::fake([
            'graph.facebook.com/*/messages' => Http::response([
                'messages' => [
                    ['id' => 'wamid.MEDIA123'],
                ],
            ], 200),
        ]);

        $data = [
            'to' => '+5511988888888',
            'media_url' => 'https://example.com/image.jpg',
            'media_type' => 'image',
            'caption' => 'Confira esta imagem',
        ];

        $response = $this->postJson("/api/social/whatsapp/accounts/{$this->account->id}/send-media", $data);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message_id',
                'conversation',
            ]);

        $this->assertDatabaseHas('whatsapp_messages', [
            'content' => 'Confira esta imagem',
            'direction' => 'outbound',
            'type' => 'image',
            'media_url' => 'https://example.com/image.jpg',
            'status' => 'sent',
        ]);
    });

    test('validates required fields for text message', function () {
        $response = $this->postJson("/api/social/whatsapp/accounts/{$this->account->id}/send", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['to', 'message']);
    });

    test('validates required fields for media message', function () {
        $response = $this->postJson("/api/social/whatsapp/accounts/{$this->account->id}/send-media", [
            'to' => '+5511988888888',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['media_url', 'media_type']);
    });

    test('cannot send message from inactive account', function () {
        $this->account->update(['is_active' => false]);

        $response = $this->postJson("/api/social/whatsapp/accounts/{$this->account->id}/send", [
            'to' => '+5511988888888',
            'message' => 'Test',
        ]);

        $response->assertStatus(422);
    });
});

describe('WhatsApp Webhooks', function () {
    test('webhook verification responds with challenge', function () {
        config(['services.whatsapp.webhook_verify_token' => 'test_verify_token']);

        $response = $this->get('/api/webhooks/whatsapp/verify?'.http_build_query([
            'hub.mode' => 'subscribe',
            'hub.verify_token' => 'test_verify_token',
            'hub.challenge' => 'challenge_12345',
        ]));

        $response->assertOk();
        expect($response->getContent())->toBe('challenge_12345');
    });

    test('webhook verification fails with wrong token', function () {
        config(['services.whatsapp.webhook_verify_token' => 'test_verify_token']);

        $response = $this->get('/api/webhooks/whatsapp/verify?'.http_build_query([
            'hub.mode' => 'subscribe',
            'hub.verify_token' => 'wrong_token',
            'hub.challenge' => 'challenge_12345',
        ]));

        $response->assertForbidden();
    });

    test('webhook handles incoming text message', function () {
        Queue::fake();

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'BUSINESS_ACCOUNT_ID',
                    'changes' => [
                        [
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => $this->account->phone_number,
                                    'phone_number_id' => $this->account->phone_number_id,
                                ],
                                'contacts' => [
                                    [
                                        'profile' => ['name' => 'João Silva'],
                                        'wa_id' => '5511988888888',
                                    ],
                                ],
                                'messages' => [
                                    [
                                        'from' => '5511988888888',
                                        'id' => 'wamid.TEST123',
                                        'timestamp' => '1642258200',
                                        'type' => 'text',
                                        'text' => ['body' => 'Olá, preciso de ajuda'],
                                    ],
                                ],
                            ],
                            'field' => 'messages',
                        ],
                    ],
                ],
            ],
        ];

        $signature = 'sha256='.hash_hmac(
            'sha256',
            json_encode($payload),
            config('services.whatsapp.app_secret')
        );

        $response = $this->postJson(
            '/api/webhooks/whatsapp/handle',
            $payload,
            ['X-Hub-Signature-256' => $signature]
        );

        $response->assertOk();

        Queue::assertPushed(\App\Jobs\Social\ProcessIncomingWhatsAppMessageJob::class);
    });

    test('webhook rejects invalid signature', function () {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [],
        ];

        $response = $this->postJson(
            '/api/webhooks/whatsapp/handle',
            $payload,
            ['X-Hub-Signature-256' => 'sha256=invalid_signature']
        );

        $response->assertForbidden();
    });

    test('webhook handles status update', function () {
        $message = WhatsAppMessage::factory()->create([
            'whatsapp_conversation_id' => WhatsAppConversation::factory()->create([
                'whatsapp_account_id' => $this->account->id,
            ])->id,
            'message_id' => 'wamid.STATUS123',
            'status' => 'sent',
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'BUSINESS_ACCOUNT_ID',
                    'changes' => [
                        [
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => $this->account->phone_number,
                                    'phone_number_id' => $this->account->phone_number_id,
                                ],
                                'statuses' => [
                                    [
                                        'id' => 'wamid.STATUS123',
                                        'status' => 'delivered',
                                        'timestamp' => '1642258300',
                                        'recipient_id' => '5511988888888',
                                    ],
                                ],
                            ],
                            'field' => 'messages',
                        ],
                    ],
                ],
            ],
        ];

        $signature = 'sha256='.hash_hmac(
            'sha256',
            json_encode($payload),
            config('services.whatsapp.app_secret')
        );

        $response = $this->postJson(
            '/api/webhooks/whatsapp/handle',
            $payload,
            ['X-Hub-Signature-256' => $signature]
        );

        $response->assertOk();

        $this->assertDatabaseHas('whatsapp_messages', [
            'message_id' => 'wamid.STATUS123',
            'status' => 'delivered',
        ]);
    });
});

describe('CRM Integration', function () {
    test('conversation auto-links to lead by phone', function () {
        $lead = Lead::factory()->create([
            'company_id' => $this->company->id,
            'phone' => '+5511988888888',
        ]);

        Queue::fake();
        Http::fake([
            'graph.facebook.com/*/messages' => Http::response([
                'messages' => [['id' => 'wamid.TEST123']],
            ], 200),
        ]);

        $response = $this->postJson("/api/social/whatsapp/accounts/{$this->account->id}/send", [
            'to' => '+5511988888888',
            'message' => 'Test message',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('whatsapp_conversations', [
            'contact_phone' => '+5511988888888',
            'lead_id' => $lead->id,
        ]);
    });

    test('conversation creates activity for linked lead', function () {
        $lead = Lead::factory()->create([
            'company_id' => $this->company->id,
            'phone' => '+5511988888888',
        ]);

        $conversation = WhatsAppConversation::factory()->create([
            'whatsapp_account_id' => $this->account->id,
            'lead_id' => $lead->id,
        ]);

        WhatsAppMessage::factory()->create([
            'whatsapp_conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'content' => 'Test message',
        ]);

        // Activity creation is tested in the ProcessIncomingWhatsAppMessageJob
        expect($conversation->lead_id)->toBe($lead->id);
    });
});
