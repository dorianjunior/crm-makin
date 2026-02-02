<?php

declare(strict_types=1);

namespace App\Listeners\CMS;

use App\Events\CMS\ContentPublished;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyContentPublished implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ContentPublished $event): void
    {
        $content = $event->content;
        $site = $content->site;

        // Notify all users of the company about the publication
        $companyId = $site->company_id;

        $users = \App\Models\User::where('company_id', $companyId)
            ->where('id', '!=', $event->userId)
            ->get();

        foreach ($users as $user) {
            $this->notificationService->create([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'type' => 'cms_published',
                'channel' => 'in_app',
                'title' => 'Conteúdo publicado',
                'message' => "O conteúdo '{$content->title}' foi publicado com sucesso.",
                'data' => [
                    'content_type' => class_basename($content),
                    'content_id' => $content->id,
                    'site_id' => $site->id,
                    'action_url' => "/cms/{$site->id}/".strtolower(class_basename($content))."s/{$content->id}",
                ],
            ]);
        }
    }
}
