<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SmsNotificationChannel implements NotificationChannelInterface
{
    public function send(Notification $notification): bool
    {
        try {
            $user = $notification->user;

            if (! $user || ! $user->phone) {
                throw new \Exception('User phone not found');
            }

            // TODO: Implement SMS logic
            // This would integrate with Twilio, Nexmo, or similar service

            Log::info('SMS notification sent', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'phone' => $user->phone,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SMS notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
