<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PipelineStage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'pipeline_id',
        'name',
        'order',
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
}
