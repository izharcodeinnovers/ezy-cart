<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'qty' => $this->qty,
            'note' => $this->note,
            'created_by' => $this->created_by,
            'is_inbound' => $this->isInbound(),
            'is_outbound' => $this->isOutbound(),
            'product' => new ProductResource($this->whenLoaded('product')),
            'variant' => new ProductVariantResource($this->whenLoaded('variant')),
            'created_by_user' => new UserResource($this->whenLoaded('createdBy')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}