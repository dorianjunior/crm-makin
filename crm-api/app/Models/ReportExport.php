<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReportExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'user_id',
        'format',
        'filename',
        'file_path',
        'file_size',
        'status',
        'error_message',
        'filters_used',
        'rows_count',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'filters_used' => 'array',
        'file_size' => 'integer',
        'rows_count' => 'integer',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($export) {
            if (empty($export->expires_at)) {
                // Default expiration: 7 days
                $export->expires_at = now()->addDays(7);
            }
        });

        static::deleting(function ($export) {
            // Delete file when record is deleted
            if ($export->file_path && Storage::exists($export->file_path)) {
                Storage::delete($export->file_path);
            }
        });
    }

    /**
     * Relationships
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Methods
     */
    public function markAsProcessing(): void
    {
        $this->status = 'processing';
        $this->save();
    }

    public function markAsCompleted(int $rowsCount = null, int $fileSize = null): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        
        if ($rowsCount !== null) {
            $this->rows_count = $rowsCount;
        }
        
        if ($fileSize !== null) {
            $this->file_size = $fileSize;
        }
        
        $this->save();
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->status = 'failed';
        $this->error_message = $errorMessage;
        $this->save();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function getDownloadUrl(): ?string
    {
        if (!$this->isCompleted() || $this->isExpired() || !$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    public function fileExists(): bool
    {
        return $this->file_path && Storage::exists($this->file_path);
    }

    public function deleteFile(): bool
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }
        
        return false;
    }

    public function getFileSizeFormatted(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function extendExpiration(int $days = 7): void
    {
        $this->expires_at = now()->addDays($days);
        $this->save();
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'completed' => 'Concluído',
            'failed' => 'Falhou',
        ];
    }

    /**
     * Get available formats
     */
    public static function getAvailableFormats(): array
    {
        return [
            'pdf' => 'PDF',
            'excel' => 'Excel (XLSX)',
            'csv' => 'CSV',
        ];
    }
}
