<?php

namespace App\Http\Resources\CMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'parent_id' => $this->parent_id,
            'label' => $this->label,
            'url' => $this->url,
            'target' => $this->target,
            'icon' => $this->icon,
            'css_class' => $this->css_class,
            'order' => $this->order,
            'active' => $this->active,
            'children' => $this->whenLoaded('children', fn () => MenuItemResource::collection($this->children)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
