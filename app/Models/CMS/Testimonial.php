<?php

namespace App\Models\CMS;

use App\Enums\ContentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'author_name',
        'author_position',
        'author_company',
        'author_avatar',
        'content',
        'rating',
        'status',
        'order',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'published_at' => 'datetime',
        'status' => ContentStatus::class,
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): MorphMany
    {
        return $this->morphMany(ContentVersion::class, 'versionable');
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(ContentApproval::class, 'approvable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ContentStatus::DRAFT);
    }

    public function scopePending($query)
    {
        return $query->where('status', ContentStatus::PENDING);
    }

    public function scopeForSite($query, int $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeHighRated($query, int $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::PUBLISHED;
    }
}
