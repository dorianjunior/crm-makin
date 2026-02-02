<?php

namespace App\Http\Resources\CMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_id' => $this->site_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'client_name' => $this->client_name,
            'project_url' => $this->project_url,
            'images' => $this->images,
            'technologies' => $this->technologies,
            'completion_date' => $this->completion_date?->format('Y-m-d'),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'order' => $this->order,
            'created_by' => $this->created_by,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ]),
            'site' => $this->whenLoaded('site', fn () => [
                'id' => $this->site->id,
                'name' => $this->site->name,
                'domain' => $this->site->domain,
            ]),
            'versions' => $this->whenLoaded('versions', fn () => ContentVersionResource::collection($this->versions)),
            'approvals' => $this->whenLoaded('approvals', fn () => ContentApprovalResource::collection($this->approvals)),
        ];
    }
}
