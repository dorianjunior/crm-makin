<?php

namespace App\Jobs\Social;

use App\Models\Social\WhatsAppAccount;
use App\Models\Social\WhatsAppConversation;
use App\Models\Social\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIncomingWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $accountId,
        public array $messageData
    ) {
        $this->onQueue('social');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing incoming WhatsApp message', [
                'account_id' => $this->accountId,
                'message_id' => $this->messageData['id'] ?? null,
            ]);

            $account = WhatsAppAccount::find($this->accountId);

            if (! $account || ! $account->is_active) {
                Log::warning('WhatsApp account not found or inactive', [
                    'account_id' => $this->accountId,
                ]);

                return;
            }

            // Extract message information
            $messageId = $this->messageData['id'];
            $fromPhone = $this->messageData['from'];
            $timestamp = $this->messageData['timestamp'];
            $type = $this->messageData['type'];

            // Get or create conversation
            $conversation = $this->getOrCreateConversation($account, $fromPhone);

            // Extract content based on message type
            $content = $this->extractContent($type, $this->messageData);
            $mediaUrl = $this->extractMediaUrl($type, $this->messageData);
            $mediaId = $this->extractMediaId($type, $this->messageData);

            // Create message
            $message = WhatsAppMessage::create([
                'whatsapp_conversation_id' => $conversation->id,
                'message_id' => $messageId,
                'direction' => 'inbound',
                'type' => $type,
                'content' => $content,
                'media_url' => $mediaUrl,
                'media_id' => $mediaId,
                'status' => 'delivered',
                'from_phone' => $fromPhone,
                'to_phone' => $account->phone_number,
                'sent_at' => \Carbon\Carbon::createFromTimestamp($timestamp),
                'metadata' => $this->messageData,
            ]);

            // Update conversation
            $conversation->update([
                'last_message_at' => now(),
                'contact_name' => $this->messageData['profile']['name'] ?? $conversation->contact_name,
            ]);

            $conversation->incrementUnread();

            Log::info('WhatsApp message saved', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'type' => $type,
            ]);

            // Create activity in CRM if linked to lead
            if ($conversation->lead_id) {
                $this->createCRMActivity($conversation, $message);
            }

            // Download media if present
            if ($mediaId) {
                $this->downloadMedia($account, $message, $mediaId);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process incoming WhatsApp message', [
                'account_id' => $this->accountId,
                'message_data' => $this->messageData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get or create conversation
     */
    protected function getOrCreateConversation(WhatsAppAccount $account, string $fromPhone): WhatsAppConversation
    {
        $conversation = WhatsAppConversation::where('whatsapp_account_id', $account->id)
            ->where('conversation_id', $fromPhone)
            ->first();

        if (! $conversation) {
            $conversation = WhatsAppConversation::create([
                'whatsapp_account_id' => $account->id,
                'conversation_id' => $fromPhone,
                'contact_phone' => $fromPhone,
                'last_message_at' => now(),
            ]);

            // Try to link to existing lead
            $lead = \App\Models\CRM\Lead::where('phone', 'like', "%{$fromPhone}%")
                ->orWhere('phone', 'like', '%'.substr($fromPhone, -10).'%')
                ->first();

            if ($lead) {
                $conversation->update(['lead_id' => $lead->id]);

                Log::info('WhatsApp conversation linked to lead', [
                    'conversation_id' => $conversation->id,
                    'lead_id' => $lead->id,
                ]);
            }
        }

        return $conversation;
    }

    /**
     * Extract content from message
     */
    protected function extractContent(string $type, array $data): ?string
    {
        return match ($type) {
            'text' => $data['text']['body'] ?? null,
            'image', 'video', 'document' => $data[$type]['caption'] ?? null,
            'location' => sprintf(
                'Location: %s, %s - %s',
                $data['location']['latitude'],
                $data['location']['longitude'],
                $data['location']['address'] ?? 'No address'
            ),
            'contacts' => json_encode($data['contacts'] ?? []),
            default => null,
        };
    }

    /**
     * Extract media URL from message
     */
    protected function extractMediaUrl(string $type, array $data): ?string
    {
        if (in_array($type, ['image', 'video', 'audio', 'document', 'sticker'])) {
            return $data[$type]['link'] ?? null;
        }

        return null;
    }

    /**
     * Extract media ID from message
     */
    protected function extractMediaId(string $type, array $data): ?string
    {
        if (in_array($type, ['image', 'video', 'audio', 'document', 'sticker'])) {
            return $data[$type]['id'] ?? null;
        }

        return null;
    }

    /**
     * Download media from WhatsApp
     */
    protected function downloadMedia(WhatsAppAccount $account, WhatsAppMessage $message, string $mediaId): void
    {
        try {
            $service = app(\App\Services\Social\WhatsAppService::class);
            $media = $service->downloadMedia($account->id, $mediaId);

            $message->update([
                'media_url' => $media['url'],
                'media_mime_type' => $media['mime_type'],
            ]);

            Log::info('WhatsApp media downloaded', [
                'message_id' => $message->id,
                'media_id' => $mediaId,
                'url' => $media['url'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to download WhatsApp media', [
                'message_id' => $message->id,
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create activity in CRM
     */
    protected function createCRMActivity(WhatsAppConversation $conversation, WhatsAppMessage $message): void
    {
        try {
            \App\Models\CRM\Activity::create([
                'lead_id' => $conversation->lead_id,
                'type' => 'whatsapp_message',
                'description' => 'WhatsApp message received: '.($message->content ?? $message->type),
                'metadata' => [
                    'message_id' => $message->message_id,
                    'message_type' => $message->type,
                    'from_phone' => $message->from_phone,
                ],
            ]);

            Log::info('CRM activity created for WhatsApp message', [
                'lead_id' => $conversation->lead_id,
                'message_id' => $message->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create CRM activity', [
                'lead_id' => $conversation->lead_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessIncomingWhatsAppMessageJob failed permanently', [
            'account_id' => $this->accountId,
            'message_data' => $this->messageData,
            'error' => $exception->getMessage(),
        ]);
    }
}
