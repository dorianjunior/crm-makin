<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'frequency',
        'schedule_config',
        'recipients',
        'format',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'schedule_config' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($schedule) {
            if (empty($schedule->next_run_at)) {
                $schedule->calculateNextRun();
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

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)
            ->where('next_run_at', '<=', now());
    }

    public function scopeByFrequency($query, string $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Methods
     */
    public function calculateNextRun(): void
    {
        $now = Carbon::now();
        $config = $this->schedule_config ?? [];

        switch ($this->frequency) {
            case 'daily':
                $time = $config['time'] ?? '08:00';
                $next = Carbon::parse($time);
                if ($next->isPast()) {
                    $next->addDay();
                }
                $this->next_run_at = $next;
                break;

            case 'weekly':
                $dayOfWeek = $config['day_of_week'] ?? 1; // Monday
                $time = $config['time'] ?? '08:00';
                $next = Carbon::parse('next ' . $this->getDayName($dayOfWeek) . ' ' . $time);
                if ($next->isPast()) {
                    $next->addWeek();
                }
                $this->next_run_at = $next;
                break;

            case 'monthly':
                $dayOfMonth = $config['day_of_month'] ?? 1;
                $time = $config['time'] ?? '08:00';
                $next = Carbon::parse($time)->day($dayOfMonth);
                if ($next->isPast()) {
                    $next->addMonth();
                }
                $this->next_run_at = $next;
                break;

            case 'quarterly':
                $month = $now->month;
                $quarterStartMonth = (int)(floor(($month - 1) / 3) * 3) + 1;
                $next = Carbon::create($now->year, $quarterStartMonth, 1)->addMonths(3);
                $this->next_run_at = $next;
                break;

            case 'yearly':
                $month = $config['month'] ?? 1;
                $day = $config['day_of_month'] ?? 1;
                $time = $config['time'] ?? '08:00';
                $next = Carbon::create($now->year, $month, $day)->parse($time);
                if ($next->isPast()) {
                    $next->addYear();
                }
                $this->next_run_at = $next;
                break;
        }
    }

    public function markAsExecuted(): void
    {
        $this->last_run_at = now();
        $this->calculateNextRun();
        $this->save();
    }

    public function activate(): void
    {
        $this->is_active = true;
        $this->calculateNextRun();
        $this->save();
    }

    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }

    public function isDue(): bool
    {
        return $this->is_active && $this->next_run_at && $this->next_run_at->isPast();
    }

    public function hasRecipients(): bool
    {
        return !empty($this->recipients);
    }

    public function getRecipientsList(): array
    {
        return $this->recipients ?? [];
    }

    public function addRecipient(string $email): void
    {
        $recipients = $this->recipients ?? [];
        if (!in_array($email, $recipients)) {
            $recipients[] = $email;
            $this->recipients = $recipients;
            $this->save();
        }
    }

    public function removeRecipient(string $email): void
    {
        $recipients = $this->recipients ?? [];
        $recipients = array_filter($recipients, fn($r) => $r !== $email);
        $this->recipients = array_values($recipients);
        $this->save();
    }

    /**
     * Helper method to get day name
     */
    private function getDayName(int $day): string
    {
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        return $days[$day] ?? 'Monday';
    }

    /**
     * Get available frequencies
     */
    public static function getAvailableFrequencies(): array
    {
        return [
            'daily' => 'Diário',
            'weekly' => 'Semanal',
            'monthly' => 'Mensal',
            'quarterly' => 'Trimestral',
            'yearly' => 'Anual',
        ];
    }

    /**
     * Get available formats
     */
    public static function getAvailableFormats(): array
    {
        return [
            'pdf' => 'PDF',
            'excel' => 'Excel',
            'csv' => 'CSV',
        ];
    }
}
