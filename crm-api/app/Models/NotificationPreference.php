<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'whatsapp_enabled',
        'push_enabled',
        'sms_enabled',
        'in_app_enabled',
        'schedule',
        'filters',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
        'schedule' => 'array',
        'filters' => 'array',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a channel is enabled.
     */
    public function isChannelEnabled(string $channel): bool
    {
        $field = "{$channel}_enabled";
        return $this->{$field} ?? false;
    }

    /**
     * Enable a channel.
     */
    public function enableChannel(string $channel): bool
    {
        $field = "{$channel}_enabled";
        return $this->update([$field => true]);
    }

    /**
     * Disable a channel.
     */
    public function disableChannel(string $channel): bool
    {
        $field = "{$channel}_enabled";
        return $this->update([$field => false]);
    }

    /**
     * Get enabled channels.
     */
    public function getEnabledChannels(): array
    {
        $channels = [];
        
        if ($this->email_enabled) $channels[] = 'email';
        if ($this->whatsapp_enabled) $channels[] = 'whatsapp';
        if ($this->push_enabled) $channels[] = 'push';
        if ($this->sms_enabled) $channels[] = 'sms';
        if ($this->in_app_enabled) $channels[] = 'in_app';

        return $channels;
    }

    /**
     * Check if notifications should be sent at this time.
     */
    public function shouldSendNow(): bool
    {
        if (empty($this->schedule)) {
            return true;
        }

        $schedule = $this->schedule;
        $now = now();

        // Check timezone
        if (isset($schedule['timezone'])) {
            $now = $now->setTimezone($schedule['timezone']);
        }

        // Check working hours
        if (isset($schedule['working_hours'])) {
            $currentHour = $now->hour;
            $start = $schedule['working_hours']['start'] ?? 0;
            $end = $schedule['working_hours']['end'] ?? 24;

            if ($currentHour < $start || $currentHour >= $end) {
                return false;
            }
        }

        // Check days of week
        if (isset($schedule['days_of_week'])) {
            $currentDay = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday
            if (!in_array($currentDay, $schedule['days_of_week'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get preference for user and notification type.
     */
    public static function getForUser(int $userId, string $notificationType): ?self
    {
        return static::where('user_id', $userId)
            ->where('notification_type', $notificationType)
            ->first();
    }

    /**
     * Get or create preference for user and notification type.
     */
    public static function getOrCreateForUser(int $userId, string $notificationType): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'notification_type' => $notificationType,
            ],
            [
                'email_enabled' => true,
                'whatsapp_enabled' => false,
                'push_enabled' => true,
                'sms_enabled' => false,
                'in_app_enabled' => true,
            ]
        );
    }
}
