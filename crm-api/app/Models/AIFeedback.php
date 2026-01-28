<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIFeedback extends Model
{
    use HasFactory;

    protected $table = 'ai_feedback';

    protected $fillable = [
        'ai_conversation_id',
        'ai_message_id',
        'user_id',
        'rating',
        'feedback_type',
        'comment',
        'was_regenerated',
        'metadata',
    ];

    protected $casts = [
        'rating' => 'integer',
        'was_regenerated' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the conversation that owns the feedback.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'ai_conversation_id');
    }

    /**
     * Get the message that owns the feedback.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(AIMessage::class, 'ai_message_id');
    }

    /**
     * Get the user who provided the feedback.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by rating.
     */
    public function scopeRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope a query to filter by feedback type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('feedback_type', $type);
    }

    /**
     * Scope a query to only include positive feedback (rating >= 4).
     */
    public function scopePositive($query)
    {
        return $query->where('rating', '>=', 4);
    }

    /**
     * Scope a query to only include negative feedback (rating <= 2).
     */
    public function scopeNegative($query)
    {
        return $query->where('rating', '<=', 2);
    }

    /**
     * Scope a query to only include feedback that led to regeneration.
     */
    public function scopeRegenerated($query)
    {
        return $query->where('was_regenerated', true);
    }

    /**
     * Check if this is positive feedback.
     */
    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    /**
     * Check if this is negative feedback.
     */
    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }

    /**
     * Check if this is neutral feedback.
     */
    public function isNeutral(): bool
    {
        return $this->rating === 3;
    }
}
