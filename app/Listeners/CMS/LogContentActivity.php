<?php

namespace App\Listeners\CMS;

use App\Events\CMS\ApprovalApproved;
use App\Events\CMS\ApprovalRejected;
use App\Events\CMS\ApprovalRequested;
use App\Events\CMS\ContentPublished;
use App\Models\Activity;
use Illuminate\Events\Dispatcher;

class LogContentActivity
{
    /**
     * Handle content published events.
     */
    public function handleContentPublished(ContentPublished $event): void
    {
        Activity::create([
            'company_id' => $event->content->site->company_id ?? null,
            'user_id' => auth()->id(),
            'activity_type' => 'cms_publish',
            'subject_type' => get_class($event->content),
            'subject_id' => $event->content->id,
            'description' => "Conteúdo publicado: {$event->contentType} #{$event->content->id}",
            'properties' => [
                'content_type' => $event->contentType,
                'content_id' => $event->content->id,
                'title' => $event->content->title ?? $event->content->name ?? null,
            ],
        ]);
    }

    /**
     * Handle approval requested events.
     */
    public function handleApprovalRequested(ApprovalRequested $event): void
    {
        Activity::create([
            'company_id' => $event->content->site->company_id ?? null,
            'user_id' => $event->approval->requested_by,
            'activity_type' => 'cms_approval_request',
            'subject_type' => get_class($event->content),
            'subject_id' => $event->content->id,
            'description' => "Solicitação de aprovação criada: {$event->contentType} #{$event->content->id}",
            'properties' => [
                'content_type' => $event->contentType,
                'content_id' => $event->content->id,
                'approval_id' => $event->approval->id,
                'title' => $event->content->title ?? $event->content->name ?? null,
                'message' => $event->approval->message,
            ],
        ]);
    }

    /**
     * Handle approval approved events.
     */
    public function handleApprovalApproved(ApprovalApproved $event): void
    {
        Activity::create([
            'company_id' => $event->content->site->company_id ?? null,
            'user_id' => $event->approval->reviewed_by,
            'activity_type' => 'cms_approval_approved',
            'subject_type' => get_class($event->content),
            'subject_id' => $event->content->id,
            'description' => "Conteúdo aprovado: {$event->contentType} #{$event->content->id}",
            'properties' => [
                'content_type' => $event->contentType,
                'content_id' => $event->content->id,
                'approval_id' => $event->approval->id,
                'title' => $event->content->title ?? $event->content->name ?? null,
                'review_comment' => $event->approval->review_comment,
            ],
        ]);
    }

    /**
     * Handle approval rejected events.
     */
    public function handleApprovalRejected(ApprovalRejected $event): void
    {
        Activity::create([
            'company_id' => $event->content->site->company_id ?? null,
            'user_id' => $event->approval->reviewed_by,
            'activity_type' => 'cms_approval_rejected',
            'subject_type' => get_class($event->content),
            'subject_id' => $event->content->id,
            'description' => "Conteúdo rejeitado: {$event->contentType} #{$event->content->id}",
            'properties' => [
                'content_type' => $event->contentType,
                'content_id' => $event->content->id,
                'approval_id' => $event->approval->id,
                'title' => $event->content->title ?? $event->content->name ?? null,
                'review_comment' => $event->approval->review_comment,
            ],
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(ContentPublished::class, [self::class, 'handleContentPublished']);
        $events->listen(ApprovalRequested::class, [self::class, 'handleApprovalRequested']);
        $events->listen(ApprovalApproved::class, [self::class, 'handleApprovalApproved']);
        $events->listen(ApprovalRejected::class, [self::class, 'handleApprovalRejected']);
    }
}
