<?php

namespace App\Services\Notifications;

use App\Models\Notification\Notification;

interface NotificationChannelInterface
{
    public function send(Notification $notification): bool;
}
