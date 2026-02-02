<?php

namespace App\Services\Social;

use App\Contracts\MessageServiceInterface;
use App\Models\Social\WhatsAppAccount;
use App\Models\Social\WhatsAppConversation;
use App\Models\Social\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppService implements MessageServiceInterface
{
    protected string $apiUrl = 'https://graph.facebook.com/v18.0';

    /**
     * Send a message via WhatsApp
     */
    public function sendMessage(int $accountId, string $recipientPhone, string $content): array
    {
        $account = WhatsAppAccount::findOrFail($accountId);

        $url = "{$this->apiUrl}/{$account->phone_number_id}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->normalizePhone($recipientPhone),
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $content,
            ],
        ];

        try {
            $response = Http::withToken($account->decrypted_token)
                ->post($url, $payload);

            if ($response->failed()) {
                Log::error('WhatsApp send message failed', [
                    'account_id' => $account->id,
                    'response' => $response->json(),
                ]);

                throw new \Exception($response->json()['error']['message'] ?? 'Failed to send message');
            }

            $data = $response->json();

            // Save outbound message
            $conversation = $this->getOrCreateConversation($account, $recipientPhone);

            $message = WhatsAppMessage::create([
                'whatsapp_conversation_id' => $conversation->id,
                'message_id' => $data['messages'][0]['id'],
                'direction' => 'outbound',
                'type' => 'text',
                'content' => $content,
                'status' => 'sent',
                'from_phone' => $account->phone_number,
                'to_phone' => $recipientPhone,
                'sent_at' => now(),
                'metadata' => $data,
            ]);

            return $message->toArray();
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send media message (image, video, document, audio)
     */
    public function sendMediaMessage(int $accountId, string $recipientPhone, string $mediaUrl, string $mediaType, ?string $caption = null): array
    {
        $account = WhatsAppAccount::findOrFail($accountId);

        $url = "{$this->apiUrl}/{$account->phone_number_id}/messages";

        $mediaPayload = ['link' => $mediaUrl];

        if ($caption && in_array($mediaType, ['image', 'video', 'document'])) {
            $mediaPayload['caption'] = $caption;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->normalizePhone($recipientPhone),
            'type' => $mediaType,
            $mediaType => $mediaPayload,
        ];

        $response = Http::withToken($account->decrypted_token)
            ->post($url, $payload);

        if ($response->failed()) {
            throw new \Exception($response->json()['error']['message'] ?? 'Failed to send media');
        }

        $data = $response->json();

        $conversation = $this->getOrCreateConversation($account, $recipientPhone);

        $message = WhatsAppMessage::create([
            'whatsapp_conversation_id' => $conversation->id,
            'message_id' => $data['messages'][0]['id'],
            'direction' => 'outbound',
            'type' => $mediaType,
            'content' => $caption,
            'media_url' => $mediaUrl,
            'status' => 'sent',
            'from_phone' => $account->phone_number,
            'to_phone' => $recipientPhone,
            'sent_at' => now(),
            'metadata' => $data,
        ]);

        return $message->toArray();
    }

    /**
     * Fetch messages from WhatsApp
     */
    public function fetchMessages(int $accountId, ?int $limit = 50): array
    {
        $account = WhatsAppAccount::findOrFail($accountId);

        return WhatsAppMessage::whereHas('conversation', function ($query) use ($account) {
            $query->where('whatsapp_account_id', $account->id);
        })
            ->with('conversation')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get conversations for an account
     */
    public function getConversations(int $accountId, ?int $limit = 50): array
    {
        $account = WhatsAppAccount::findOrFail($accountId);

        return $account->conversations()
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByDesc('last_message_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Update message status (delivered, read, failed)
     */
    public function updateMessageStatus(string $messageId, string $status, ?string $errorMessage = null): void
    {
        $message = WhatsAppMessage::where('message_id', $messageId)->first();

        if ($message) {
            $message->updateStatus($status, $errorMessage);

            Log::info('WhatsApp message status updated', [
                'message_id' => $messageId,
                'status' => $status,
            ]);
        }
    }

    /**
     * Mark message as read (send read receipt to WhatsApp)
     */
    public function markAsRead(int $accountId, string $messageId): bool
    {
        $account = WhatsAppAccount::findOrFail($accountId);

        $url = "{$this->apiUrl}/{$account->phone_number_id}/messages";

        $response = Http::withToken($account->decrypted_token)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'status' => 'read',
                'message_id' => $messageId,
            ]);

        return $response->successful();
    }

    /**
     * Check if account is connected
     */
    public function isConnected(int $accountId): bool
    {
        $account = WhatsAppAccount::find($accountId);

        return $account && $account->is_active;
    }

    /**
     * Disconnect account
     */
    public function disconnect(int $accountId): bool
    {
        $account = WhatsAppAccount::find($accountId);

        if (! $account) {
            return false;
        }

        $account->update(['is_active' => false]);

        return true;
    }

    /**
     * Get or create conversation
     */
    protected function getOrCreateConversation(WhatsAppAccount $account, string $phone): WhatsAppConversation
    {
        $normalizedPhone = $this->normalizePhone($phone);

        $conversation = WhatsAppConversation::where('whatsapp_account_id', $account->id)
            ->where('conversation_id', $normalizedPhone)
            ->first();

        if (! $conversation) {
            $conversation = WhatsAppConversation::create([
                'whatsapp_account_id' => $account->id,
                'conversation_id' => $normalizedPhone,
                'contact_phone' => $normalizedPhone,
                'last_message_at' => now(),
            ]);

            // Try to link to existing lead
            $this->linkConversationToLead($conversation);
        }

        return $conversation;
    }

    /**
     * Link conversation to lead by phone number
     */
    protected function linkConversationToLead(WhatsAppConversation $conversation): void
    {
        $lead = \App\Models\CRM\Lead::where('phone', 'like', "%{$conversation->contact_phone}%")
            ->orWhere('phone', 'like', "%".substr($conversation->contact_phone, -10)."%") // Last 10 digits
            ->first();

        if ($lead) {
            $conversation->update(['lead_id' => $lead->id]);

            Log::info('WhatsApp conversation linked to lead', [
                'conversation_id' => $conversation->id,
                'lead_id' => $lead->id,
            ]);
        }
    }

    /**
     * Normalize phone number (remove spaces, dashes, add country code if missing)
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add Brazil country code if not present and has 11 digits (cellphone) or 10 (landline)
        if (strlen($phone) === 11 || strlen($phone) === 10) {
            $phone = '55' . $phone; // Brazil country code
        }

        return $phone;
    }

    /**
     * Download media from WhatsApp
     */
    public function downloadMedia(int $accountId, string $mediaId): array
    {
        $account = WhatsAppAccount::findOrFail($accountId);

        // Step 1: Get media URL
        $response = Http::withToken($account->decrypted_token)
            ->get("{$this->apiUrl}/{$mediaId}");

        if ($response->failed()) {
            throw new \Exception('Failed to get media URL');
        }

        $mediaUrl = $response->json()['url'];
        $mimeType = $response->json()['mime_type'];

        // Step 2: Download media file
        $mediaResponse = Http::withToken($account->decrypted_token)
            ->get($mediaUrl);

        if ($mediaResponse->failed()) {
            throw new \Exception('Failed to download media');
        }

        // Step 3: Save to storage
        $extension = $this->getExtensionFromMimeType($mimeType);
        $filename = "whatsapp/media/{$mediaId}.{$extension}";

        Storage::disk('public')->put($filename, $mediaResponse->body());

        return [
            'path' => $filename,
            'url' => Storage::disk('public')->url($filename),
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Get file extension from MIME type
     */
    protected function getExtensionFromMimeType(string $mimeType): string
    {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/3gpp' => '3gp',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/amr' => 'amr',
            'application/pdf' => 'pdf',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        return $mimeMap[$mimeType] ?? 'bin';
    }
}
