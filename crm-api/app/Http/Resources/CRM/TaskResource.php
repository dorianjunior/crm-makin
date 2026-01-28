<?php

namespace App\Http\Resources\CRM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'lead_id' => $this->lead_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->toISOString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'is_overdue' => $this->due_date && $this->due_date < now() && ! $this->status->isClosed(),
            'lead' => $this->whenLoaded('lead', fn () => new LeadResource($this->lead)),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
