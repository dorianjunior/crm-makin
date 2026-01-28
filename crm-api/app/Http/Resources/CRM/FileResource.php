<?php

namespace App\Http\Resources\CRM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'filename' => $this->filename,
            'path' => $this->path,
            'size' => $this->size,
            'size_formatted' => $this->getHumanReadableSize(),
            'mime_type' => $this->mime_type,
            'url' => $this->getUrl(),
            'lead' => $this->whenLoaded('lead', fn () => [
                'id' => $this->lead->id,
                'name' => $this->lead->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function getHumanReadableSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2).' '.$units[$unit];
    }

    private function getUrl(): string
    {
        return asset('storage/'.$this->path);
    }
}
