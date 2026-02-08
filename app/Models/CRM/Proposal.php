<?php

namespace App\Models\CRM;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'number',
        'total_value',
        'status',
        'notes',
        'valid_until',
        'sent_at',
    ];

    protected $casts = [
        'total_value' => 'decimal:2',
        'status' => ProposalStatus::class,
        'valid_until' => 'date',
        'sent_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProposalItem::class);
    }
}
