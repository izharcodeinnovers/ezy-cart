<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'address_id' => $this->address_id,
            'coupon_id' => $this->coupon_id,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'shipping_amount' => $this->shipping_amount,
            'grand_total' => $this->grand_total,
            'payment_status' => $this->payment_status?->value,
            'payment_status_label' => $this->payment_status?->label(),
            'order_status' => $this->order_status?->value,
            'order_status_label' => $this->order_status?->label(),
            'shipping_method' => $this->shipping_method?->value,
            'payment_method' => $this->payment_method?->value,
            'notes' => $this->notes,
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_refunded' => $this->canBeRefunded(),
            'user' => new UserResource($this->whenLoaded('user')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'shipment' => new ShipmentResource($this->whenLoaded('shipment')),
            'coupon' => new CouponResource($this->whenLoaded('coupon')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}