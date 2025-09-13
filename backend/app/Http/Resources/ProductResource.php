<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
                'feedback_count' => $this->feedback_count,
                'rating' => $this->rating,
            ],
            'details' => $this->when($this->relationLoaded('productDetail') && $this->productDetail, function () {
                return $this->productDetail;
            }),
        ];

        return $product;
    }
}
