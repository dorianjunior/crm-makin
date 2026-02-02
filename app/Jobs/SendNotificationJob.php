<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\Notifications\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        public int $notificationId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $service): void
    {
        try {
            $notification = Notification::find($this->notificationId);

            if (! $notification) {
                Log::warning('Notification not found', [
                    'notification_id' => $this->notificationId,
                ]);

                return;
            }

            // Check if already sent
            if ($notification->isSent()) {
                Log::info('Notification already sent', [
                    'notification_id' => $this->notificationId,
                ]);

                return;
            }

            // Send notification
            $success = $service->send($notification);

            if ($success) {
                Log::info('Notification sent successfully', [
                    'notification_id' => $this->notificationId,
                    'channel' => $notification->channel,
                ]);
            } else {
                Log::warning('Notification send failed', [
                    'notification_id' => $this->notificationId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending notification', [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage(),
            ]);

            // Mark as failed after max retries
            if ($this->attempts() >= $this->tries) {
                $notification = Notification::find($this->notificationId);
                if ($notification) {
                    $notification->markAsFailed($e->getMessage());
                }
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Notification job failed permanently', [
            'notification_id' => $this->notificationId,
            'error' => $exception->getMessage(),
        ]);

        $notification = Notification::find($this->notificationId);
        if ($notification) {
            $notification->markAsFailed($exception->getMessage());
        }
    }
}
