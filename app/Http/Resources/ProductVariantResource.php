<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'size' => $this->size,
            'color' => $this->color,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_in_stock' => $this->isInStock(),
            'display_name' => $this->getDisplayName(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}