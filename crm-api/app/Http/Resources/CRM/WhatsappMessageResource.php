<?php

namespace App\Http\Resources\CRM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsappMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'message' => $this->message,
            'sent_at' => $this->sent_at?->toISOString(),
            'lead' => $this->whenLoaded('lead', fn () => new LeadResource($this->lead)),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
