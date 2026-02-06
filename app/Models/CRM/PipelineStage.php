<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'order',
        'probability',
        'color',
    ];

    protected $casts = [
        'probability' => 'integer',
    ];

    protected $appends = [
        'leads_count',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_pipeline')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function getLeadsCountAttribute(): int
    {
        return $this->leads()->count();
    }
}
