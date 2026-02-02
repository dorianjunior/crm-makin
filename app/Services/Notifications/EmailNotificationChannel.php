<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationChannel implements NotificationChannelInterface
{
    public function send(Notification $notification): bool
    {
        try {
            $user = $notification->user;

            if (! $user || ! $user->email) {
                throw new \Exception('User email not found');
            }

            Mail::send([], [], function ($message) use ($notification, $user) {
                $message->to($user->email)
                    ->subject($notification->title)
                    ->html($this->formatMessage($notification));
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Email notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function formatMessage(Notification $notification): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #4F46E5; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
                    .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                    .footer { background: #f3f4f6; padding: 15px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 5px 5px; }
                    .priority-high { border-left: 4px solid #ef4444; }
                    .priority-urgent { border-left: 4px solid #dc2626; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2 style='margin: 0;'>{$notification->title}</h2>
                    </div>
                    <div class='content priority-{$notification->priority}'>
                        ".nl2br($notification->message)."
                    </div>
                    <div class='footer'>
                        <p>Esta é uma notificação automática. Por favor, não responda este email.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}
