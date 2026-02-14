<?php

namespace App\Services\Notifications;

use App\Models\Notification\Notification;
use App\Models\Notification\NotificationPreference;
use App\Models\Notification\NotificationTemplate;
use App\Models\Admin\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected array $channels = [];

    public function __construct()
    {
        // Register available channels
        $this->channels = [
            'email' => EmailNotificationChannel::class,
            'whatsapp' => WhatsAppNotificationChannel::class,
            'push' => PushNotificationChannel::class,
            'sms' => SmsNotificationChannel::class,
            'in_app' => InAppNotificationChannel::class,
        ];
    }

    /**
     * Send a notification.
     */
    public function send(Notification $notification): bool
    {
        try {
            // Check if user has preferences
            if ($notification->user_id) {
                $preference = NotificationPreference::getForUser(
                    $notification->user_id,
                    $notification->type
                );

                // Check if channel is enabled
                if ($preference && ! $preference->isChannelEnabled($notification->channel)) {
                    Log::info('Notification channel disabled by user preference', [
                        'notification_id' => $notification->id,
                        'channel' => $notification->channel,
                    ]);

                    return false;
                }

                // Check schedule
                if ($preference && ! $preference->shouldSendNow()) {
                    // Reschedule for later
                    $notification->update(['scheduled_at' => now()->addHour()]);

                    return false;
                }
            }

            // Get channel class
            $channelClass = $this->channels[$notification->channel] ?? null;

            if (! $channelClass) {
                throw new \Exception("Channel {$notification->channel} not supported");
            }

            // Send via channel
            $channel = new $channelClass();
            $result = $channel->send($notification);

            if ($result) {
                $notification->markAsSent();
                Log::info('Notification sent successfully', [
                    'notification_id' => $notification->id,
                    'channel' => $notification->channel,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            $notification->markAsFailed($e->getMessage());

            return false;
        }
    }

    /**
     * Create and send a notification.
     */
    public function create(array $data): ?Notification
    {
        $notification = Notification::create($data);

        // Send immediately if not scheduled
        if (! $notification->scheduled_at || $notification->scheduled_at->isPast()) {
            $this->send($notification);
        }

        return $notification;
    }

    /**
     * Create notification from template.
     */
    public function createFromTemplate(
        int $companyId,
        string $type,
        string $channel,
        User $user,
        $notifiable = null,
        array $data = [],
        string $priority = 'normal'
    ): ?Notification {
        // Get template
        $template = NotificationTemplate::getByTypeAndChannel($companyId, $type, $channel);

        if (! $template) {
            Log::warning('No template found', [
                'company_id' => $companyId,
                'type' => $type,
                'channel' => $channel,
            ]);

            return null;
        }

        // Validate required variables
        if (! $template->hasAllRequiredVariables($data)) {
            Log::error('Missing required variables for template', [
                'template_id' => $template->id,
                'required' => $template->getRequiredVariables(),
                'provided' => array_keys($data),
            ]);

            return null;
        }

        // Render template
        $subject = $template->renderSubject($data);
        $body = $template->renderBody($data);

        // Create notification
        return $this->create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->id,
            'type' => $type,
            'channel' => $channel,
            'title' => $subject ?? $type,
            'message' => $body,
            'data' => $data,
            'priority' => $priority,
        ]);
    }

    /**
     * Send notification to multiple users.
     */
    public function sendBulk(array $users, string $type, array $channels, array $data): array
    {
        $results = [];

        foreach ($users as $user) {
            foreach ($channels as $channel) {
                $notification = $this->createFromTemplate(
                    $user->company_id,
                    $type,
                    $channel,
                    $user,
                    null,
                    $data
                );

                $results[] = [
                    'user_id' => $user->id,
                    'channel' => $channel,
                    'notification_id' => $notification?->id,
                    'success' => $notification !== null,
                ];
            }
        }

        return $results;
    }

    /**
     * Retry failed notifications.
     */
    public function retryFailed(int $maxRetries = 3): int
    {
        $failed = Notification::failed()
            ->where('retry_count', '<', $maxRetries)
            ->get();

        $retried = 0;

        foreach ($failed as $notification) {
            $notification->incrementRetry();
            $notification->update(['status' => 'pending']);

            if ($this->send($notification)) {
                $retried++;
            }
        }

        return $retried;
    }

    /**
     * Process scheduled notifications.
     */
    public function processScheduled(): int
    {
        $scheduled = Notification::readyToSend()->get();

        $processed = 0;

        foreach ($scheduled as $notification) {
            if ($this->send($notification)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Mark multiple notifications as read.
     */
    public function markAsRead(array $notificationIds): int
    {
        return Notification::whereIn('id', $notificationIds)
            ->whereNull('read_at')
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
    }

    /**
     * Delete old notifications.
     */
    public function deleteOld(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->where('status', 'read')
            ->delete();
    }
}
