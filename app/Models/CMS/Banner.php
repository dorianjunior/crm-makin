<?php

namespace App\Models\CMS;

use App\Enums\ContentStatus;
use App\Models\Admin\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'title',
        'location',
        'image',
        'link_url',
        'new_window',
        'alt_text',
        'start_date',
        'end_date',
        'status',
        'order',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'new_window' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function scopeActive($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    public function scopeForSite($query, int $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::PUBLISHED;
    }

    public function isActive(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        $now = now();

        if ($this->start_date && $this->start_date > $now) {
            return false;
        }

        if ($this->end_date && $this->end_date < $now) {
            return false;
        }

        return true;
    }
}
