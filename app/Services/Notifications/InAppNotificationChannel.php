<?php

namespace App\Services\Notifications;

use App\Models\Notification\Notification;
use Illuminate\Support\Facades\Log;

class InAppNotificationChannel implements NotificationChannelInterface
{
    public function send(Notification $notification): bool
    {
        try {
            // In-app notifications are just stored in database
            // They will be fetched by the frontend

            Log::info('In-app notification created', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('In-app notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
