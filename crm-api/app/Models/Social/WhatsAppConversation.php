<?php

namespace App\Models\Social;

use App\Models\CRM\Lead;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'whatsapp_account_id',
        'lead_id',
        'conversation_id',
        'contact_name',
        'contact_phone',
        'contact_profile_pic',
        'is_group',
        'unread_count',
        'last_message_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'last_message_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relationship: WhatsApp Account
     */
    public function whatsappAccount(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class);
    }

    /**
     * Relationship: Lead
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Relationship: Messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    /**
     * Scope: Active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: With unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    /**
     * Scope: Recent conversations
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('last_message_at', '>=', now()->subDays($days));
    }

    /**
     * Mark all messages as read
     */
    public function markAsRead(): void
    {
        $this->update(['unread_count' => 0]);

        $this->messages()
            ->where('direction', 'inbound')
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'status' => 'read']);
    }

    /**
     * Increment unread count
     */
    public function incrementUnread(): void
    {
        $this->increment('unread_count');
    }
}
