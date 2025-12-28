<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total' => (float) $this->total,
            'product' => new ProductResource($this->whenLoaded('product')),
            'attributes' => $this->when($this->product_attributes, function () {
                return $this->product_attributes ?? [];
            }),
            /*
            'attributes_details' => $this->whenLoaded('attributes', function () {
                return $this->attributes->map(function ($attribute) {
                    return [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                        'value' => $attribute->value,
                        'price' => (float) $attribute->price,
                    ];
                });
            }),
            */
        ];
    }
}
