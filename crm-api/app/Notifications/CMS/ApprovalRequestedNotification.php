<?php

namespace App\Notifications\CMS;

use App\Models\CMS\ContentApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Model $content,
        public string $contentType,
        public ContentApproval $approval
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contentTitle = $this->content->title ?? $this->content->name ?? "Item #{$this->content->id}";

        return (new MailMessage)
            ->subject("Nova Solicitação de Aprovação - {$this->contentType}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Uma nova solicitação de aprovação foi criada.")
            ->line("**Tipo de Conteúdo:** {$this->contentType}")
            ->line("**Título:** {$contentTitle}")
            ->line("**Solicitado por:** {$this->approval->requestedBy->name}")
            ->line("**Comentário:** {$this->approval->comment}")
            ->action('Revisar Solicitação', url("/admin/cms/{$this->contentType}/{$this->content->id}"))
            ->line('Por favor, revise e aprove ou rejeite esta solicitação.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'content_type' => $this->contentType,
            'content_id' => $this->content->id,
            'approval_id' => $this->approval->id,
            'requested_by' => $this->approval->requested_by,
            'comment' => $this->approval->comment,
        ];
    }
}
