<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected $appends = [
        'stages_count',
        'leads_count',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order');
    }

    public function leads(): HasManyThrough
    {
        return $this->hasManyThrough(
            Lead::class,
            PipelineStage::class,
            'pipeline_id',
            'stage_id',
            'id',
            'id'
        );
    }

    public function getStagesCountAttribute(): int
    {
        return $this->stages()->count();
    }

    public function getLeadsCountAttribute(): int
    {
        return $this->stages()->withCount('leads')->get()->sum('leads_count');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When a pipeline is set as default, unset all others
        static::saving(function ($pipeline) {
            if ($pipeline->is_default) {
                static::where('company_id', $pipeline->company_id)
                    ->where('id', '!=', $pipeline->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
