<?php

namespace App\Events\CRM;

use App\Models\CRM\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event disparado quando um novo lead é criado
 *
 * Para ativar WebSockets:
 * 1. composer require pusher/pusher-php-server
 * 2. Configurar .env com credenciais Pusher ou Soketi
 * 3. Descomentar BroadcastServiceProvider em config/app.php
 */
class LeadCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Lead $lead
    ) {}

    /**
     * Canal onde o evento será transmitido
     * Privado por empresa para segurança
     */
    public function broadcastOn(): Channel
    {
        return new Channel('company.'.$this->lead->company_id);
    }

    /**
     * Nome do evento no frontend
     */
    public function broadcastAs(): string
    {
        return 'lead.created';
    }

    /**
     * Dados enviados ao frontend
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->lead->id,
            'name' => $this->lead->name,
            'email' => $this->lead->email,
            'phone' => $this->lead->phone,
            'company' => $this->lead->company,
            'status' => $this->lead->status->value,
            'status_label' => $this->lead->status->label(),
            'source' => $this->lead->source ? [
                'id' => $this->lead->source->id,
                'name' => $this->lead->source->name,
            ] : null,
            'assigned_user' => $this->lead->assignedUser ? [
                'id' => $this->lead->assignedUser->id,
                'name' => $this->lead->assignedUser->name,
            ] : null,
            'created_at' => $this->lead->created_at->toISOString(),
        ];
    }
}
