<?php

namespace App\Jobs\Social;

use App\Models\Social\InstagramAccount;
use App\Models\Social\InstagramMessage;
use App\Services\Social\InstagramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIncomingInstagramMessageJob implements ShouldQueue
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
        public array $messageData
    ) {
        $this->onQueue('social');
    }

    /**
     * Execute the job.
     */
    public function handle(InstagramService $instagramService): void
    {
        try {
            Log::info('Processing incoming Instagram message', [
                'data' => $this->messageData,
            ]);

            // Extract message information
            $senderId = $this->messageData['sender']['id'] ?? null;
            $recipientId = $this->messageData['recipient']['id'] ?? null;
            $messageId = $this->messageData['message']['mid'] ?? null;
            $messageText = $this->messageData['message']['text'] ?? null;
            $timestamp = $this->messageData['timestamp'] ?? null;

            if (! $senderId || ! $recipientId || ! $messageId) {
                Log::warning('Invalid message data received', [
                    'data' => $this->messageData,
                ]);

                return;
            }

            // Find Instagram account by recipient ID (business account)
            $account = InstagramAccount::where('instagram_user_id', $recipientId)
                ->active()
                ->first();

            if (! $account) {
                Log::warning('Instagram account not found for recipient', [
                    'recipient_id' => $recipientId,
                ]);

                return;
            }

            // Create or update message
            $message = InstagramMessage::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'instagram_account_id' => $account->id,
                    'conversation_id' => $senderId, // Use sender ID as conversation ID for DMs
                    'sender_id' => $senderId,
                    'sender_username' => $this->messageData['sender']['username'] ?? null,
                    'direction' => 'inbound',
                    'type' => $this->getMessageType($this->messageData),
                    'content' => $messageText,
                    'media_url' => $this->messageData['message']['attachments'][0]['payload']['url'] ?? null,
                    'status' => 'delivered',
                    'sent_at' => $timestamp ? \Carbon\Carbon::createFromTimestamp($timestamp / 1000) : now(),
                    'metadata' => $this->messageData,
                ]
            );

            Log::info('Instagram message saved', [
                'message_id' => $message->id,
                'sender' => $senderId,
            ]);

            // Try to link message to existing lead
            if ($message->lead_id === null) {
                $leadId = $instagramService->linkMessageToLead($message);

                if ($leadId) {
                    Log::info('Instagram message linked to lead', [
                        'message_id' => $message->id,
                        'lead_id' => $leadId,
                    ]);
                }
            }

            // TODO: Trigger notification to user about new message
            // TODO: Create activity log in CRM
        } catch (\Exception $e) {
            Log::error('Failed to process incoming Instagram message', [
                'data' => $this->messageData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Determine message type from data
     */
    protected function getMessageType(array $data): string
    {
        if (isset($data['message']['attachments'])) {
            $attachmentType = $data['message']['attachments'][0]['type'] ?? 'text';

            return match ($attachmentType) {
                'image' => 'image',
                'video' => 'video',
                'audio' => 'audio',
                default => 'text',
            };
        }

        if (isset($data['message']['is_echo']) && $data['message']['is_echo']) {
            return 'text';
        }

        if (isset($data['story'])) {
            return isset($data['story']['mention']) ? 'story_mention' : 'story_reply';
        }

        return 'text';
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessIncomingInstagramMessageJob failed permanently', [
            'data' => $this->messageData,
            'error' => $exception->getMessage(),
        ]);
    }
}
