<?php

namespace App\Models\CMS;

use App\Enums\ContentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'created_by',
        'title',
        'slug',
        'excerpt',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'featured_image',
        'status',
        'published_at',
        'meta_data',
        'order',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'published_at' => 'datetime',
        'meta_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::updating(function ($page) {
            if ($page->isDirty('title') && empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

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
        return $query->where('status', ContentStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ContentStatus::DRAFT);
    }

    public function scopePending($query)
    {
        return $query->where('status', ContentStatus::PENDING);
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::PUBLISHED
            && $this->published_at !== null
            && $this->published_at <= now();
    }
}
