<?php

namespace App\Jobs\Social;

use App\Models\Social\WhatsAppMessage;
use App\Services\Social\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
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
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $accountId,
        public string $recipientPhone,
        public string $content,
        public ?string $mediaUrl = null,
        public ?string $mediaType = null
    ) {
        $this->onQueue('social');
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsappService): void
    {
        try {
            Log::info('Sending WhatsApp message', [
                'account_id' => $this->accountId,
                'recipient' => $this->recipientPhone,
                'has_media' => $this->mediaUrl !== null,
            ]);

            if ($this->mediaUrl && $this->mediaType) {
                $message = $whatsappService->sendMediaMessage(
                    $this->accountId,
                    $this->recipientPhone,
                    $this->mediaUrl,
                    $this->mediaType,
                    $this->content
                );
            } else {
                $message = $whatsappService->sendMessage(
                    $this->accountId,
                    $this->recipientPhone,
                    $this->content
                );
            }

            Log::info('WhatsApp message sent successfully', [
                'message_id' => $message['message_id'] ?? null,
                'account_id' => $this->accountId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'account_id' => $this->accountId,
                'recipient' => $this->recipientPhone,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // If it's the last attempt, mark as failed
            if ($this->attempts() >= $this->tries) {
                $this->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Mark message as failed
     */
    protected function markAsFailed(string $errorMessage): void
    {
        // Try to find the pending message and mark as failed
        $message = WhatsAppMessage::where('to_phone', $this->recipientPhone)
            ->where('status', 'pending')
            ->where('content', $this->content)
            ->latest()
            ->first();

        if ($message) {
            $message->updateStatus('failed', $errorMessage);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsAppMessageJob failed permanently', [
            'account_id' => $this->accountId,
            'recipient' => $this->recipientPhone,
            'content' => substr($this->content, 0, 100),
            'error' => $exception->getMessage(),
        ]);

        $this->markAsFailed($exception->getMessage());
    }
}
