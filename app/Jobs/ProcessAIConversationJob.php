<?php

namespace App\Jobs;

use App\Models\AIConversation;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAIConversationJob implements ShouldQueue
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
    public int $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $conversationId,
        public string $userMessage,
        public array $variables = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $conversation = AIConversation::findOrFail($this->conversationId);

            // Get AI service
            $service = GeminiService::forCompany($conversation->company_id);

            // Save user message
            $userMessage = $conversation->addMessage([
                'role' => 'user',
                'content' => $this->userMessage,
            ]);

            // Get conversation history
            $history = $conversation->messages()
                ->whereIn('role', ['user', 'assistant'])
                ->orderBy('created_at', 'asc')
                ->take(20)
                ->get()
                ->map(fn ($msg) => $msg->toContextArray())
                ->toArray();

            // Generate AI response
            $response = $service->chat($this->userMessage, $history);

            // Save assistant message
            $conversation->addMessage([
                'role' => 'assistant',
                'content' => $response['content'],
                'input_tokens' => $response['tokens']['input'],
                'output_tokens' => $response['tokens']['output'],
                'total_tokens' => $response['tokens']['total'],
                'cost' => $response['cost'],
                'processing_time_ms' => $response['processing_time_ms'],
                'model_version' => $response['model_version'],
            ]);

            Log::info('AI conversation processed successfully', [
                'conversation_id' => $this->conversationId,
                'tokens' => $response['tokens'],
                'cost' => $response['cost'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process AI conversation', [
                'conversation_id' => $this->conversationId,
                'error' => $e->getMessage(),
            ]);

            // Mark conversation as failed after all retries
            if ($this->attempts() >= $this->tries) {
                $conversation = AIConversation::find($this->conversationId);
                if ($conversation) {
                    $conversation->markAsFailed();
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
        Log::error('AI conversation job failed permanently', [
            'conversation_id' => $this->conversationId,
            'error' => $exception->getMessage(),
        ]);

        $conversation = AIConversation::find($this->conversationId);
        if ($conversation) {
            $conversation->markAsFailed();
        }
    }
}
