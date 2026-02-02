<?php

namespace App\Models\Social;

use App\Models\CRM\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstagramAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'instagram_user_id',
        'username',
        'access_token',
        'token_expires_at',
        'account_type',
        'profile_picture_url',
        'followers_count',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'access_token',
    ];

    /**
     * Relationship: Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship: Messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(InstagramMessage::class);
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
     * Check if token is expired
     */
    public function isTokenExpired(): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Get encrypted access token
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
}
