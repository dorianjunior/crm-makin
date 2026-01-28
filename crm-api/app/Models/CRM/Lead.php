<?php

namespace App\Models\CRM;

use App\Enums\LeadStatus;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'source_id',
        'assigned_to',
        'name',
        'email',
        'phone',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function pipelineStages(): BelongsToMany
    {
        return $this->belongsToMany(PipelineStage::class, 'lead_pipeline')
            ->withPivot('position')
            ->withTimestamps();
    }
}
