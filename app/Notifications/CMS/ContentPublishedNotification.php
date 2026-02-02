<?php

namespace App\Notifications\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Model $content,
        public string $contentType
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contentTitle = $this->content->title ?? $this->content->name ?? "Item #{$this->content->id}";

        return (new MailMessage())
            ->subject("Conteúdo Publicado - {$this->contentType}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Seu conteúdo foi publicado com sucesso.')
            ->line("**Tipo de Conteúdo:** {$this->contentType}")
            ->line("**Título:** {$contentTitle}")
            ->line("**Publicado em:** {$this->content->published_at->format('d/m/Y H:i')}")
            ->action('Visualizar Conteúdo', url("/admin/cms/{$this->contentType}/{$this->content->id}"))
            ->line('Seu conteúdo está agora disponível publicamente.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'content_type' => $this->contentType,
            'content_id' => $this->content->id,
            'published_at' => $this->content->published_at,
        ];
    }
}
