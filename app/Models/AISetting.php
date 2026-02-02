<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AISetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'provider',
        'model',
        'api_key',
        'temperature',
        'max_tokens',
        'top_p',
        'top_k',
        'stop_sequences',
        'safety_settings',
        'custom_parameters',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'top_p' => 'float',
        'top_k' => 'integer',
        'stop_sequences' => 'array',
        'safety_settings' => 'array',
        'custom_parameters' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the company that owns the AI setting.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include active settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include settings for a specific provider.
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Get the default AI setting for a company.
     */
    public static function getDefaultForCompany(int $companyId): ?self
    {
        return static::where('company_id', $companyId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get configuration array for AI service.
     */
    public function getConfig(): array
    {
        return [
            'provider' => $this->provider,
            'model' => $this->model,
            'api_key' => $this->api_key,
            'temperature' => $this->temperature,
            'max_tokens' => $this->max_tokens,
            'top_p' => $this->top_p,
            'top_k' => $this->top_k,
            'stop_sequences' => $this->stop_sequences,
            'safety_settings' => $this->safety_settings,
            'custom_parameters' => $this->custom_parameters,
        ];
    }

    /**
     * Set this setting as the default for the company.
     */
    public function setAsDefault(): bool
    {
        // Remove default flag from all other settings in this company
        static::where('company_id', $this->company_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this as default
        return $this->update(['is_default' => true]);
    }
}
