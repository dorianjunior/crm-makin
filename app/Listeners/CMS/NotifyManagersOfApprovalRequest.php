<?php

declare(strict_types=1);

namespace App\Listeners\CMS;

use App\Events\CMS\ApprovalRequested;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyManagersOfApprovalRequest implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ApprovalRequested $event): void
    {
        $approval = $event->approval;
        $content = $approval->approvable;
        $site = $content->site;

        // TODO: Get all managers of the company
        // For now, we'll notify the company owner or admin users
        $companyId = $site->company_id;

        // Get users who can approve (managers/admins of the company)
        $managers = \App\Models\User::where('company_id', $companyId)
            ->where('id', '!=', $approval->requested_by)
            ->get();

        foreach ($managers as $manager) {
            $this->notificationService->create([
                'user_id' => $manager->id,
                'company_id' => $companyId,
                'type' => 'cms_approval',
                'channel' => 'in_app',
                'title' => 'Nova solicitação de aprovação',
                'message' => "Novo conteúdo aguardando aprovação: {$content->title}",
                'data' => [
                    'approval_id' => $approval->id,
                    'content_type' => class_basename($content),
                    'content_id' => $content->id,
                    'site_id' => $site->id,
                    'action_url' => "/cms/approvals/{$approval->id}",
                ],
            ]);
        }
    }
}
