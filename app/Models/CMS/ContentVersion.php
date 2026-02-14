<?php

namespace App\Models\CMS;

use App\Models\Admin\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'versionable_type',
        'versionable_id',
        'created_by',
        'version_number',
        'content_data',
        'change_summary',
    ];

    protected $casts = [
        'content_data' => 'array',
    ];

    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('version_number', 'desc');
    }

    public function scopeForContent($query, string $type, int $id)
    {
        return $query->where('versionable_type', $type)
            ->where('versionable_id', $id);
    }
}
