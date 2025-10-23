<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = [
            'id' => $this->id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'photo' => $this->photo,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'price' => [
                'discount' => $this->discount,
                'base_price' => $this->base_price
            ],
            'tags' => $this->tags,
            'feedbacks' => [
                'rating' => $this->rating,
                'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            ],
            'details' => $this->whenLoaded('productDetail'),
            'attributes' => $this->when(
                $this->relationLoaded('productAttribute') && $this->productAttribute->isNotEmpty(),
                fn() => $this->productAttribute
                    ->groupBy('attr_key')
                    ->map(fn($group) => $group->map(fn($item) => Arr::except($item->toArray(), ['attr_key', 'product_id'])))
                    ->toArray()
            ),
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'status' => $this->status,
        ];

        return $product;
    }
}
