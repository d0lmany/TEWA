<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $shop = [
            'id' => $this->id,
            'name' => $this->name,
            'picture' => $this->picture ? url('storage/' . $this->picture) : null,
            'description' => $this->description,
            'seller' => new SellerResource($this->whenLoaded('seller')),
            'rating' => $this->when($this->rating, $this->rating),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'reviewsCount' => $this->when($this->reviewsCount, $this->reviewsCount)
        ];

        return $shop;
    }
}
