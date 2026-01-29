<?php

namespace App\Jobs;

use App\Services\Reports\ExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanOldExportsJob implements ShouldQueue
{
    use Queueable;

    private int $days;

    /**
     * Create a new job instance.
     */
    public function __construct(int $days = 7)
    {
        $this->days = $days;
    }

    /**
     * Execute the job.
     */
    public function handle(ExportService $exportService): void
    {
        Log::info("Cleaning exports older than {$this->days} days");

        $count = $exportService->cleanOldExports($this->days);

        Log::info("Cleaned {$count} old exports");
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("CleanOldExportsJob failed: {$exception->getMessage()}");
    }
}
