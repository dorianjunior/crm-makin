<?php

declare(strict_types=1);

namespace App\Listeners\CMS;

use App\Events\CMS\ApprovalProcessed;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApprovalProcessed implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ApprovalProcessed $event): void
    {
        $approval = $event->approval;
        $content = $approval->approvable;
        $requesterId = $approval->requested_by;
        $action = $event->action;

        $title = $action === 'approved'
            ? 'Conteúdo aprovado'
            : 'Conteúdo rejeitado';

        $message = $action === 'approved'
            ? "Seu conteúdo '{$content->title}' foi aprovado e publicado."
            : "Seu conteúdo '{$content->title}' foi rejeitado. Motivo: {$approval->review_notes}";

        $this->notificationService->create([
            'user_id' => $requesterId,
            'company_id' => $content->site->company_id,
            'type' => 'cms_approval_processed',
            'channel' => 'in_app',
            'title' => $title,
            'message' => $message,
            'data' => [
                'approval_id' => $approval->id,
                'content_type' => class_basename($content),
                'content_id' => $content->id,
                'action' => $action,
                'action_url' => "/cms/approvals/{$approval->id}",
            ],
        ]);
    }
}
