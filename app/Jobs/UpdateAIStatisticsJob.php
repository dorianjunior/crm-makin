<?php

namespace App\Jobs;

use App\Models\AI\AIConversation;
use App\Models\AI\AIPromptTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAIStatisticsJob implements ShouldQueue
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
     * Create a new job instance.
     */
    public function __construct(
        public int $companyId,
        public ?int $templateId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->templateId) {
                $this->updateTemplateStatistics($this->templateId);
            } else {
                $this->updateCompanyStatistics($this->companyId);
            }

            Log::info('AI statistics updated successfully', [
                'company_id' => $this->companyId,
                'template_id' => $this->templateId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update AI statistics', [
                'company_id' => $this->companyId,
                'template_id' => $this->templateId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update statistics for a specific template.
     */
    protected function updateTemplateStatistics(int $templateId): void
    {
        $template = AIPromptTemplate::findOrFail($templateId);

        // Get average satisfaction from conversations
        $avgSatisfaction = AIConversation::where('ai_prompt_template_id', $templateId)
            ->whereNotNull('user_satisfaction_rating')
            ->avg('user_satisfaction_rating');

        // Get usage count
        $usageCount = AIConversation::where('ai_prompt_template_id', $templateId)->count();

        $template->update([
            'usage_count' => $usageCount,
            'avg_satisfaction_rating' => $avgSatisfaction ? round($avgSatisfaction, 2) : null,
        ]);
    }

    /**
     * Update statistics for all templates in a company.
     */
    protected function updateCompanyStatistics(int $companyId): void
    {
        $templates = AIPromptTemplate::where('company_id', $companyId)->get();

        foreach ($templates as $template) {
            $this->updateTemplateStatistics($template->id);
        }

        // Update conversation durations
        $this->updateConversationDurations($companyId);

        // Calculate aggregate metrics
        $this->calculateAggregateMetrics($companyId);
    }

    /**
     * Update conversation durations for completed conversations.
     */
    protected function updateConversationDurations(int $companyId): void
    {
        AIConversation::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereNull('duration_seconds')
            ->whereNotNull('first_message_at')
            ->whereNotNull('last_message_at')
            ->chunk(100, function ($conversations) {
                foreach ($conversations as $conversation) {
                    $duration = $conversation->last_message_at->diffInSeconds($conversation->first_message_at);
                    $conversation->update(['duration_seconds' => $duration]);
                }
            });
    }

    /**
     * Calculate aggregate metrics for the company.
     */
    protected function calculateAggregateMetrics(int $companyId): void
    {
        $metrics = DB::table('ai_conversations')
            ->where('company_id', $companyId)
            ->selectRaw('
                COUNT(*) as total_conversations,
                SUM(message_count) as total_messages,
                SUM(total_input_tokens) as total_input_tokens,
                SUM(total_output_tokens) as total_output_tokens,
                SUM(total_cost) as total_cost,
                AVG(user_satisfaction_rating) as avg_satisfaction,
                COUNT(CASE WHEN lead_converted = 1 THEN 1 END) as converted_leads,
                COUNT(CASE WHEN lead_id IS NOT NULL THEN 1 END) as conversations_with_leads
            ')
            ->first();

        // Store or log aggregate metrics
        Log::info('Company AI metrics', [
            'company_id' => $companyId,
            'metrics' => $metrics,
        ]);

        // You could store these in a separate aggregates table if needed
    }
}
