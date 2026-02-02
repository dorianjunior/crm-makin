<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AIPromptTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'category',
        'description',
        'system_prompt',
        'user_prompt_template',
        'variables',
        'tags',
        'is_active',
        'usage_count',
        'avg_satisfaction_rating',
    ];

    protected $casts = [
        'variables' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'avg_satisfaction_rating' => 'float',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Get the company that owns the template.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the conversations using this template.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(AIConversation::class, 'ai_prompt_template_id');
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to search by tags.
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Render the user prompt with variables.
     */
    public function renderUserPrompt(array $variables = []): string
    {
        $prompt = $this->user_prompt_template;

        foreach ($variables as $key => $value) {
            $prompt = str_replace('{{'.$key.'}}', $value, $prompt);
        }

        return $prompt;
    }

    /**
     * Get required variables from the template.
     */
    public function getRequiredVariables(): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $this->user_prompt_template, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Validate if all required variables are provided.
     */
    public function hasAllRequiredVariables(array $variables): bool
    {
        $required = $this->getRequiredVariables();

        return empty(array_diff($required, array_keys($variables)));
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Update satisfaction rating.
     */
    public function updateSatisfactionRating(float $newRating): void
    {
        $currentAvg = $this->avg_satisfaction_rating ?? 0;
        $currentCount = $this->usage_count;

        if ($currentCount > 0) {
            $newAvg = (($currentAvg * $currentCount) + $newRating) / ($currentCount + 1);
        } else {
            $newAvg = $newRating;
        }

        $this->update(['avg_satisfaction_rating' => round($newAvg, 2)]);
    }
}
