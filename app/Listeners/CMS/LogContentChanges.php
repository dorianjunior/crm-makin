<?php

declare(strict_types=1);

namespace App\Listeners\CMS;

use App\Events\CMS\ContentCreated;
use App\Events\CMS\ContentPublished;
use App\Events\CMS\ContentUpdated;
use Illuminate\Support\Facades\Log;

class LogContentChanges
{
    /**
     * Handle content created event.
     */
    public function handleCreated(ContentCreated $event): void
    {
        $content = $event->content;

        Log::info('CMS Content Created', [
            'type' => class_basename($content),
            'id' => $content->id,
            'title' => $content->title ?? 'N/A',
            'site_id' => $content->site_id ?? null,
            'user_id' => $event->userId,
        ]);
    }

    /**
     * Handle content updated event.
     */
    public function handleUpdated(ContentUpdated $event): void
    {
        $content = $event->content;

        Log::info('CMS Content Updated', [
            'type' => class_basename($content),
            'id' => $content->id,
            'title' => $content->title ?? 'N/A',
            'site_id' => $content->site_id ?? null,
            'user_id' => $event->userId,
            'changes' => $event->changes,
        ]);
    }

    /**
     * Handle content published event.
     */
    public function handlePublished(ContentPublished $event): void
    {
        $content = $event->content;

        Log::info('CMS Content Published', [
            'type' => class_basename($content),
            'id' => $content->id,
            'title' => $content->title ?? 'N/A',
            'site_id' => $content->site_id ?? null,
            'user_id' => $event->userId,
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(
            ContentCreated::class,
            [LogContentChanges::class, 'handleCreated']
        );

        $events->listen(
            ContentUpdated::class,
            [LogContentChanges::class, 'handleUpdated']
        );

        $events->listen(
            ContentPublished::class,
            [LogContentChanges::class, 'handlePublished']
        );
    }
}
