<?php

namespace App\Http\Resources\CRM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'proposal_id' => $this->proposal_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => (float) $this->price,
            'price_formatted' => 'R$ '.number_format($this->price, 2, ',', '.'),
            'subtotal' => (float) ($this->quantity * $this->price),
            'subtotal_formatted' => 'R$ '.number_format($this->quantity * $this->price, 2, ',', '.'),
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
        ];
    }
}
