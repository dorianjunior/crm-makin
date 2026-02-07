<?php

namespace App\Models\CRM;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'lead_id',
        'assigned_to',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', TaskStatus::COMPLETED);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->where('status', '!=', TaskStatus::COMPLETED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::PENDING);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::IN_PROGRESS);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::COMPLETED);
    }

    // =========================================================================
    // Accessors & Mutators
    // =========================================================================

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || $this->status === TaskStatus::COMPLETED) {
            return false;
        }

        return $this->due_date->isPast();
    }
}

