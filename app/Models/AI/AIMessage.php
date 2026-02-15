<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AIMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'function_call',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'cost',
        'processing_time_ms',
        'model_version',
        'metadata',
    ];

    protected $casts = [
        'function_call' => 'array',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'total_tokens' => 'integer',
        'cost' => 'decimal:6',
        'processing_time_ms' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'ai_conversation_id');
    }

    /**
     * Get the feedback for this message.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(AIFeedback::class, 'ai_message_id');
    }

    /**
     * Scope a query to only include messages by role.
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include user messages.
     */
    public function scopeUser($query)
    {
        return $query->where('role', 'user');
    }

    /**
     * Scope a query to only include assistant messages.
     */
    public function scopeAssistant($query)
    {
        return $query->where('role', 'assistant');
    }

    /**
     * Scope a query to only include system messages.
     */
    public function scopeSystem($query)
    {
        return $query->where('role', 'system');
    }

    /**
     * Check if this is a user message.
     */
    public function isUserMessage(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if this is an assistant message.
     */
    public function isAssistantMessage(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Check if this is a system message.
     */
    public function isSystemMessage(): bool
    {
        return $this->role === 'system';
    }

    /**
     * Check if this message has a function call.
     */
    public function hasFunctionCall(): bool
    {
        return ! empty($this->function_call);
    }

    /**
     * Get the cost per token.
     */
    public function getCostPerTokenAttribute(): float
    {
        if ($this->total_tokens === 0) {
            return 0;
        }

        return round($this->cost / $this->total_tokens, 8);
    }

    /**
     * Get processing time in seconds.
     */
    public function getProcessingTimeSecondsAttribute(): float
    {
        return round($this->processing_time_ms / 1000, 3);
    }

    /**
     * Check if the message has feedback.
     */
    public function hasFeedback(): bool
    {
        return $this->feedback()->exists();
    }

    /**
     * Get average rating from feedback.
     */
    public function getAverageRating(): ?float
    {
        $avg = $this->feedback()->avg('rating');

        return $avg ? round($avg, 2) : null;
    }

    /**
     * Format message for AI context.
     */
    public function toContextArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
