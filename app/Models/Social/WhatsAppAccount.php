<?php

namespace App\Models\Social;

use App\Models\CRM\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'phone_number_id',
        'business_account_id',
        'phone_number',
        'display_name',
        'access_token',
        'verify_token',
        'account_type',
        'quality_rating',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'access_token',
        'verify_token',
    ];

    /**
     * Relationship: Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship: Conversations
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(WhatsAppConversation::class);
    }

    /**
     * Scope: Active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: For company
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get decrypted access token
     */
    public function getDecryptedTokenAttribute(): ?string
    {
        if (! $this->access_token) {
            return null;
        }

        return decrypt($this->access_token);
    }

    /**
     * Set encrypted access token
     */
    public function setAccessTokenAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['access_token'] = encrypt($value);
        } else {
            $this->attributes['access_token'] = null;
        }
    }

    /**
     * Check if account has good quality rating
     */
    public function hasGoodQuality(): bool
    {
        return in_array($this->quality_rating, ['GREEN', null]);
    }
}
