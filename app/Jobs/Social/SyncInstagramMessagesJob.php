<?php

namespace App\Jobs\Social;

use App\Models\Social\InstagramAccount;
use App\Services\Social\InstagramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncInstagramMessagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $accountId
    ) {
        $this->onQueue('social');
    }

    /**
     * Execute the job.
     */
    public function handle(InstagramService $instagramService): void
    {
        try {
            $account = InstagramAccount::find($this->accountId);

            if (! $account || ! $account->is_active) {
                Log::warning('Instagram account not found or inactive', [
                    'account_id' => $this->accountId,
                ]);

                return;
            }

            // Check if token needs refresh
            if ($account->isTokenExpired()) {
                Log::info('Refreshing expired Instagram token', [
                    'account_id' => $account->id,
                ]);
                $account = $instagramService->refreshAccessToken($account);
            }

            // Fetch new messages
            Log::info('Syncing Instagram messages', [
                'account_id' => $account->id,
                'username' => $account->username,
            ]);

            $messages = $instagramService->fetchMessages($account->id);

            Log::info('Instagram messages synced', [
                'account_id' => $account->id,
                'count' => count($messages),
            ]);

            // Try to link messages to leads
            foreach ($messages as $message) {
                if ($message->lead_id === null && $message->isInbound()) {
                    $instagramService->linkMessageToLead($message);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync Instagram messages', [
                'account_id' => $this->accountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SyncInstagramMessagesJob failed permanently', [
            'account_id' => $this->accountId,
            'error' => $exception->getMessage(),
        ]);
    }
}
