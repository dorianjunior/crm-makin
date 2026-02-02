<?php

namespace App\Models\Social;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'whatsapp_conversation_id',
        'message_id',
        'direction',
        'type',
        'content',
        'media_url',
        'media_mime_type',
        'media_id',
        'status',
        'error_message',
        'from_phone',
        'to_phone',
        'sent_at',
        'delivered_at',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relationship: Conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'whatsapp_conversation_id');
    }

    /**
     * Scope: Inbound messages
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /**
     * Scope: Outbound messages
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    /**
     * Scope: By status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Failed messages
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Recent messages
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Check if message is inbound
     */
    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    /**
     * Check if message is outbound
     */
    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    /**
     * Check if message has media
     */
    public function hasMedia(): bool
    {
        return in_array($this->type, ['image', 'video', 'audio', 'document', 'sticker']);
    }

    /**
     * Update message status
     */
    public function updateStatus(string $status, ?string $errorMessage = null): void
    {
        $updates = ['status' => $status];

        if ($errorMessage) {
            $updates['error_message'] = $errorMessage;
        }

        if ($status === 'delivered') {
            $updates['delivered_at'] = now();
        } elseif ($status === 'read') {
            $updates['read_at'] = now();
        }

        $this->update($updates);
    }
}
