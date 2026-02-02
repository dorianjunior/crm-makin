<?php

namespace App\Http\Resources\CRM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'total_value' => (float) $this->total_value,
            'total_value_formatted' => 'R$ '.number_format($this->total_value, 2, ',', '.'),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'can_edit' => $this->status->canEdit(),
            'lead' => $this->whenLoaded('lead', fn () => new LeadResource($this->lead)),
            'items' => $this->whenLoaded('items', fn () => ProposalItemResource::collection($this->items)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
