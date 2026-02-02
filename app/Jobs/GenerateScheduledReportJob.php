<?php

namespace App\Jobs;

use App\Models\ReportSchedule;
use App\Services\Reports\ExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateScheduledReportJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ExportService $exportService): void
    {
        // Get all due schedules
        $schedules = ReportSchedule::due()->get();

        Log::info("Processing {$schedules->count()} scheduled reports");

        foreach ($schedules as $schedule) {
            try {
                $report = $schedule->report;

                // Generate export
                $export = $exportService->export(
                    $report,
                    $schedule->format,
                    $report->user_id,
                    $report->filters ?? []
                );

                // Send to recipients if configured
                if ($schedule->hasRecipients() && $export->isCompleted()) {
                    $this->sendToRecipients($schedule, $export);
                }

                // Mark schedule as executed
                $schedule->markAsExecuted();

                Log::info("Scheduled report {$report->id} generated successfully");
            } catch (\Exception $e) {
                Log::error("Failed to generate scheduled report {$schedule->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Send export to recipients
     */
    private function sendToRecipients(ReportSchedule $schedule, $export): void
    {
        $recipients = $schedule->getRecipientsList();
        $report = $schedule->report;

        foreach ($recipients as $recipient) {
            try {
                Mail::raw(
                    "Olá,\n\nSegue em anexo o relatório '{$report->name}' gerado automaticamente.\n\nAtenciosamente,\nCRM Makin",
                    function ($message) use ($recipient, $report, $export) {
                        $message->to($recipient)
                            ->subject("Relatório: {$report->name}")
                            ->attach(storage_path('app/'.$export->file_path), [
                                'as' => $export->filename,
                            ]);
                    }
                );

                Log::info("Report sent to {$recipient}");
            } catch (\Exception $e) {
                Log::error("Failed to send report to {$recipient}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateScheduledReportJob failed: {$exception->getMessage()}");
    }
}
