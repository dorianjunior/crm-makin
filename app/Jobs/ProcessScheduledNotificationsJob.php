<?php

namespace App\Jobs;

use App\Services\Notifications\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScheduledNotificationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $service): void
    {
        try {
            Log::info('Processing scheduled notifications');

            // Process scheduled notifications
            $processed = $service->processScheduled();

            Log::info('Scheduled notifications processed', [
                'count' => $processed,
            ]);

            // Retry failed notifications
            $retried = $service->retryFailed();

            Log::info('Failed notifications retried', [
                'count' => $retried,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing scheduled notifications', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
