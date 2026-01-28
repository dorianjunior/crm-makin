<?php

namespace App\Enums;

enum AIProvider: string
{
    case GEMINI = 'gemini';
    case OPENAI = 'openai';
    case CLAUDE = 'claude';

    /**
     * Get all possible values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::GEMINI => 'Google Gemini',
            self::OPENAI => 'OpenAI GPT',
            self::CLAUDE => 'Anthropic Claude',
        };
    }

    /**
     * Get default model for the provider
     */
    public function defaultModel(): string
    {
        return match ($this) {
            self::GEMINI => 'gemini-1.5-flash',
            self::OPENAI => 'gpt-4-turbo',
            self::CLAUDE => 'claude-3-sonnet',
        };
    }

    /**
     * Get API endpoint base URL
     */
    public function apiEndpoint(): string
    {
        return match ($this) {
            self::GEMINI => 'https://generativelanguage.googleapis.com/v1beta',
            self::OPENAI => 'https://api.openai.com/v1',
            self::CLAUDE => 'https://api.anthropic.com/v1',
        };
    }

    /**
     * Check if provider is available
     */
    public function isAvailable(): bool
    {
        return match ($this) {
            self::GEMINI => ! empty(config('services.gemini.api_key')),
            self::OPENAI => ! empty(config('services.openai.api_key')),
            self::CLAUDE => ! empty(config('services.claude.api_key')),
        };
    }
}
