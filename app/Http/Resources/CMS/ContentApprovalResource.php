<?php

declare(strict_types=1);

namespace App\Http\Resources\CMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentApprovalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'approvable_type' => $this->approvable_type,
            'approvable_id' => $this->approvable_id,
            'status' => $this->status,
            'requested_by' => $this->requested_by,
            'reviewed_by' => $this->reviewed_by,
            'message' => $this->message,
            'review_notes' => $this->review_notes,
            'requested_at' => $this->requested_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'approvable' => $this->whenLoaded('approvable', function () {
                $approvable = $this->approvable;

                if ($approvable instanceof \App\Models\CMS\Page) {
                    return new PageResource($approvable);
                }

                if ($approvable instanceof \App\Models\CMS\Post) {
                    return new PostResource($approvable);
                }

                return $approvable;
            }),
            'requester' => $this->whenLoaded('requestedBy', function () {
                return [
                    'id' => $this->requestedBy->id,
                    'name' => $this->requestedBy->name,
                    'email' => $this->requestedBy->email,
                ];
            }),
            'reviewer' => $this->whenLoaded('reviewedBy', function () {
                return [
                    'id' => $this->reviewedBy->id,
                    'name' => $this->reviewedBy->name,
                    'email' => $this->reviewedBy->email,
                ];
            }),
        ];
    }
}
