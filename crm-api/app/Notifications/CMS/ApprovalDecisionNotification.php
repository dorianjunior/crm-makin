<?php

namespace App\Notifications\CMS;

use App\Models\CMS\ContentApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalDecisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Model $content,
        public string $contentType,
        public ContentApproval $approval,
        public bool $approved
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contentTitle = $this->content->title ?? $this->content->name ?? "Item #{$this->content->id}";
        $decision = $this->approved ? 'aprovada' : 'rejeitada';
        $decisionUpper = $this->approved ? 'Aprovada' : 'Rejeitada';

        $mail = (new MailMessage)
            ->subject("Solicitação {$decisionUpper} - {$this->contentType}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Sua solicitação de aprovação foi {$decision}.")
            ->line("**Tipo de Conteúdo:** {$this->contentType}")
            ->line("**Título:** {$contentTitle}")
            ->line("**Decisão por:** {$this->approval->reviewedBy->name}")
            ->line("**Data da Decisão:** {$this->approval->reviewed_at->format('d/m/Y H:i')}");

        if ($this->approval->review_comment) {
            $mail->line("**Comentário:** {$this->approval->review_comment}");
        }

        return $mail
            ->action('Visualizar Conteúdo', url("/admin/cms/{$this->contentType}/{$this->content->id}"))
            ->line($this->approved
                ? 'Seu conteúdo foi publicado e está disponível.'
                : 'Por favor, revise os comentários e faça as alterações necessárias.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'content_type' => $this->contentType,
            'content_id' => $this->content->id,
            'approval_id' => $this->approval->id,
            'approved' => $this->approved,
            'reviewed_by' => $this->approval->reviewed_by,
            'review_comment' => $this->approval->review_comment,
            'reviewed_at' => $this->approval->reviewed_at,
        ];
    }
}
