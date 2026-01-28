<?php

namespace App\Http\Resources\CMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_id' => $this->site_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'fields' => $this->fields,
            'settings' => $this->settings,
            'submit_button_text' => $this->submit_button_text,
            'success_message' => $this->success_message,
            'notification_email' => $this->notification_email,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ]),
            'site' => $this->whenLoaded('site', fn() => [
                'id' => $this->site->id,
                'name' => $this->site->name,
                'domain' => $this->site->domain,
            ]),
        ];
    }
}
