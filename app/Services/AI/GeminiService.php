<?php

namespace App\Services\AI;

use App\Models\AI\AISetting;
use Google\GenerativeAI\Client as GeminiClient;
use Google\GenerativeAI\GenerationConfig;
use Google\GenerativeAI\SafetySetting;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?GeminiClient $client = null;

    protected ?AISetting $setting = null;

    protected array $conversationHistory = [];

    /**
     * Initialize the service with an AI setting.
     */
    public function __construct(?AISetting $setting = null)
    {
        if ($setting) {
            $this->setSetting($setting);
        }
    }

    /**
     * Set the AI setting for the service.
     */
    public function setSetting(AISetting $setting): self
    {
        $this->setting = $setting;
        $this->client = new GeminiClient($setting->api_key);

        return $this;
    }

    /**
     * Generate a single response from a prompt.
     *
     * @param  string  $prompt  The user prompt
     * @param  string|null  $systemPrompt  Optional system prompt
     * @return array ['content' => string, 'tokens' => array, 'cost' => float, 'processing_time_ms' => int]
     */
    public function generateResponse(string $prompt, ?string $systemPrompt = null): array
    {
        $this->validateSetting();

        $startTime = microtime(true);

        try {
            $model = $this->client->generativeModel($this->setting->model);

            // Build generation config
            $config = $this->buildGenerationConfig();

            // Build safety settings
            $safetySettings = $this->buildSafetySettings();

            // Combine system prompt with user prompt if provided
            $fullPrompt = $systemPrompt
                ? "{$systemPrompt}\n\n{$prompt}"
                : $prompt;

            // Generate response
            $response = $model->generateContent(
                $fullPrompt,
                $config,
                $safetySettings
            );

            $content = $response->text();

            // Get token counts
            $inputTokens = $response->usageMetadata->promptTokenCount ?? 0;
            $outputTokens = $response->usageMetadata->candidatesTokenCount ?? 0;
            $totalTokens = $inputTokens + $outputTokens;

            // Calculate cost
            $cost = $this->calculateCost($inputTokens, $outputTokens);

            // Calculate processing time
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            return [
                'content' => $content,
                'tokens' => [
                    'input' => $inputTokens,
                    'output' => $outputTokens,
                    'total' => $totalTokens,
                ],
                'cost' => $cost,
                'processing_time_ms' => $processingTime,
                'model_version' => $this->setting->model,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini API error', [
                'message' => $e->getMessage(),
                'setting_id' => $this->setting->id,
            ]);

            throw new \RuntimeException('Failed to generate AI response: '.$e->getMessage());
        }
    }

    /**
     * Chat with context from conversation history.
     *
     * @param  string  $userMessage  The user message
     * @param  array  $history  Optional conversation history [['role' => 'user|model', 'parts' => [['text' => '...']]]]
     * @return array ['content' => string, 'tokens' => array, 'cost' => float, 'processing_time_ms' => int]
     */
    public function chat(string $userMessage, array $history = []): array
    {
        $this->validateSetting();

        $startTime = microtime(true);

        try {
            $model = $this->client->generativeModel($this->setting->model);

            // Build generation config
            $config = $this->buildGenerationConfig();

            // Build safety settings
            $safetySettings = $this->buildSafetySettings();

            // Start chat with history
            $chat = $model->startChat($history);

            // Send message
            $response = $chat->sendMessage($userMessage, $config, $safetySettings);

            $content = $response->text();

            // Get token counts
            $inputTokens = $response->usageMetadata->promptTokenCount ?? 0;
            $outputTokens = $response->usageMetadata->candidatesTokenCount ?? 0;
            $totalTokens = $inputTokens + $outputTokens;

            // Calculate cost
            $cost = $this->calculateCost($inputTokens, $outputTokens);

            // Calculate processing time
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            return [
                'content' => $content,
                'tokens' => [
                    'input' => $inputTokens,
                    'output' => $outputTokens,
                    'total' => $totalTokens,
                ],
                'cost' => $cost,
                'processing_time_ms' => $processingTime,
                'model_version' => $this->setting->model,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini chat error', [
                'message' => $e->getMessage(),
                'setting_id' => $this->setting->id,
            ]);

            throw new \RuntimeException('Failed to generate chat response: '.$e->getMessage());
        }
    }

    /**
     * Stream response for real-time output.
     *
     * @param  string  $prompt  The user prompt
     * @param  callable  $callback  Function to call with each chunk
     * @return array ['content' => string, 'tokens' => array, 'cost' => float, 'processing_time_ms' => int]
     */
    public function streamResponse(string $prompt, callable $callback): array
    {
        $this->validateSetting();

        $startTime = microtime(true);
        $fullContent = '';

        try {
            $model = $this->client->generativeModel($this->setting->model);

            // Build generation config
            $config = $this->buildGenerationConfig();

            // Build safety settings
            $safetySettings = $this->buildSafetySettings();

            // Stream response
            $stream = $model->generateContentStream($prompt, $config, $safetySettings);

            $inputTokens = 0;
            $outputTokens = 0;

            foreach ($stream as $chunk) {
                $text = $chunk->text();
                $fullContent .= $text;

                // Call callback with chunk
                $callback($text);

                // Update token counts
                if (isset($chunk->usageMetadata)) {
                    $inputTokens = $chunk->usageMetadata->promptTokenCount ?? $inputTokens;
                    $outputTokens = $chunk->usageMetadata->candidatesTokenCount ?? $outputTokens;
                }
            }

            $totalTokens = $inputTokens + $outputTokens;
            $cost = $this->calculateCost($inputTokens, $outputTokens);
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            return [
                'content' => $fullContent,
                'tokens' => [
                    'input' => $inputTokens,
                    'output' => $outputTokens,
                    'total' => $totalTokens,
                ],
                'cost' => $cost,
                'processing_time_ms' => $processingTime,
                'model_version' => $this->setting->model,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini stream error', [
                'message' => $e->getMessage(),
                'setting_id' => $this->setting->id,
            ]);

            throw new \RuntimeException('Failed to stream AI response: '.$e->getMessage());
        }
    }

    /**
     * Count tokens in a text.
     *
     * @param  string  $text  The text to count tokens
     * @return int Token count
     */
    public function countTokens(string $text): int
    {
        $this->validateSetting();

        try {
            $model = $this->client->generativeModel($this->setting->model);
            $result = $model->countTokens($text);

            return $result->totalTokens ?? 0;
        } catch (\Exception $e) {
            Log::error('Token counting error', [
                'message' => $e->getMessage(),
                'setting_id' => $this->setting->id,
            ]);

            // Fallback: rough estimation (1 token ≈ 4 characters)
            return (int) ceil(strlen($text) / 4);
        }
    }

    /**
     * Build generation config from settings.
     */
    protected function buildGenerationConfig(): ?GenerationConfig
    {
        $config = new GenerationConfig();

        if ($this->setting->temperature !== null) {
            $config->temperature = $this->setting->temperature;
        }

        if ($this->setting->max_tokens !== null) {
            $config->maxOutputTokens = $this->setting->max_tokens;
        }

        if ($this->setting->top_p !== null) {
            $config->topP = $this->setting->top_p;
        }

        if ($this->setting->top_k !== null) {
            $config->topK = $this->setting->top_k;
        }

        if ($this->setting->stop_sequences) {
            $config->stopSequences = $this->setting->stop_sequences;
        }

        return $config;
    }

    /**
     * Build safety settings from configuration.
     */
    protected function buildSafetySettings(): array
    {
        if (! $this->setting->safety_settings) {
            return [];
        }

        $safetySettings = [];

        foreach ($this->setting->safety_settings as $category => $threshold) {
            $safetySettings[] = new SafetySetting(
                $category,
                $threshold
            );
        }

        return $safetySettings;
    }

    /**
     * Calculate cost based on token usage.
     * Gemini pricing (as of 2024): Free tier, then varies by model.
     *
     * @param  int  $inputTokens  Input token count
     * @param  int  $outputTokens  Output token count
     * @return float Cost in USD
     */
    protected function calculateCost(int $inputTokens, int $outputTokens): float
    {
        // Gemini pricing varies by model and tier
        // For simplicity, using approximate pricing for gemini-pro:
        // Input: $0.00025 / 1K tokens
        // Output: $0.0005 / 1K tokens

        $inputCost = ($inputTokens / 1000) * 0.00025;
        $outputCost = ($outputTokens / 1000) * 0.0005;

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Validate that a setting is configured.
     */
    protected function validateSetting(): void
    {
        if (! $this->setting) {
            throw new \RuntimeException('AI setting not configured. Call setSetting() first.');
        }

        if (! $this->setting->is_active) {
            throw new \RuntimeException('AI setting is not active.');
        }

        if (empty($this->setting->api_key)) {
            throw new \RuntimeException('API key not configured.');
        }
    }

    /**
     * Create service instance from company ID.
     */
    public static function forCompany(int $companyId): self
    {
        $setting = AISetting::getDefaultForCompany($companyId);

        if (! $setting) {
            throw new \RuntimeException("No active AI setting found for company {$companyId}");
        }

        return new self($setting);
    }
}
