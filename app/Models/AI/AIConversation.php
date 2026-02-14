<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AIConversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'ai_prompt_template_id',
        'lead_id',
        'conversationable_type',
        'conversationable_id',
        'conversation_id',
        'channel',
        'status',
        'provider',
        'model',
        'system_prompt',
        'message_count',
        'total_input_tokens',
        'total_output_tokens',
        'total_cost',
        'user_satisfaction_rating',
        'lead_converted',
        'first_message_at',
        'last_message_at',
        'duration_seconds',
        'context_data',
        'metadata',
    ];

    protected $casts = [
        'message_count' => 'integer',
        'total_input_tokens' => 'integer',
        'total_output_tokens' => 'integer',
        'total_cost' => 'decimal:6',
        'user_satisfaction_rating' => 'integer',
        'lead_converted' => 'boolean',
        'first_message_at' => 'datetime',
        'last_message_at' => 'datetime',
        'duration_seconds' => 'integer',
        'context_data' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the company that owns the conversation.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the prompt template used for this conversation.
     */
    public function promptTemplate(): BelongsTo
    {
        return $this->belongsTo(AIPromptTemplate::class, 'ai_prompt_template_id');
    }

    /**
     * Get the lead associated with this conversation.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the conversationable model (WhatsAppConversation or InstagramConversation).
     */
    public function conversationable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the messages in this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(AIMessage::class, 'ai_conversation_id');
    }

    /**
     * Get the feedback for this conversation.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(AIFeedback::class, 'ai_conversation_id');
    }

    /**
     * Scope a query to only include active conversations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include completed conversations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to filter by channel.
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope a query to filter by provider.
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Add a message to the conversation.
     */
    public function addMessage(array $messageData): AIMessage
    {
        $message = $this->messages()->create($messageData);

        // Update conversation statistics
        $this->increment('message_count');
        $this->increment('total_input_tokens', $message->input_tokens ?? 0);
        $this->increment('total_output_tokens', $message->output_tokens ?? 0);
        $this->increment('total_cost', $message->cost ?? 0);

        // Update timestamps
        if ($this->message_count === 1) {
            $this->update(['first_message_at' => now()]);
        }
        $this->update(['last_message_at' => now()]);

        return $message;
    }

    /**
     * Mark the conversation as completed.
     */
    public function markAsCompleted(): bool
    {
        $this->calculateDuration();

        return $this->update(['status' => 'completed']);
    }

    /**
     * Mark the conversation as failed.
     */
    public function markAsFailed(): bool
    {
        return $this->update(['status' => 'failed']);
    }

    /**
     * Mark the conversation as abandoned.
     */
    public function markAsAbandoned(): bool
    {
        return $this->update(['status' => 'abandoned']);
    }

    /**
     * Calculate conversation duration.
     */
    protected function calculateDuration(): void
    {
        if ($this->first_message_at && $this->last_message_at) {
            $duration = $this->last_message_at->diffInSeconds($this->first_message_at);
            $this->update(['duration_seconds' => $duration]);
        }
    }

    /**
     * Get total tokens used.
     */
    public function getTotalTokensAttribute(): int
    {
        return $this->total_input_tokens + $this->total_output_tokens;
    }

    /**
     * Get average tokens per message.
     */
    public function getAverageTokensPerMessageAttribute(): float
    {
        if ($this->message_count === 0) {
            return 0;
        }

        return round($this->total_tokens / $this->message_count, 2);
    }

    /**
     * Get cost per message.
     */
    public function getCostPerMessageAttribute(): float
    {
        if ($this->message_count === 0) {
            return 0;
        }

        return round($this->total_cost / $this->message_count, 4);
    }

    /**
     * Check if the conversation has feedback.
     */
    public function hasFeedback(): bool
    {
        return $this->feedback()->exists();
    }

    /**
     * Get average satisfaction rating from feedback.
     */
    public function getAverageSatisfactionRating(): ?float
    {
        $avg = $this->feedback()->avg('rating');

        return $avg ? round($avg, 2) : null;
    }
}
