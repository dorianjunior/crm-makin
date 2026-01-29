<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationChannel implements NotificationChannelInterface
{
    public function send(Notification $notification): bool
    {
        try {
            $user = $notification->user;

            if (!$user || !$user->phone) {
                throw new \Exception('User phone not found');
            }

            // Get user's WhatsApp account (if integrated)
            $whatsappAccount = $user->company->whatsappAccounts()->first();

            if (!$whatsappAccount) {
                throw new \Exception('No WhatsApp account configured for company');
            }

            $service = new WhatsAppService($whatsappAccount);

            // Format message
            $message = "*{$notification->title}*\n\n{$notification->message}";

            // Send message
            $service->sendMessage($user->phone, $message);

            return true;

        } catch (\Exception $e) {
            Log::error('WhatsApp notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
