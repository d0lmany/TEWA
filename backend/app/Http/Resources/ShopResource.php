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
            'avatar' => $this->avatar,
            'description' => $this->description,
            'seller' => $this->when($this->relationLoaded('seller') && $this->seller, function() {
                return $this->seller;
            }),
            'rating' => $this->when(isset($this->rating), $this->rating),
            'city' => $this->city,
            'payment_type' => $this->payment_type,
            'fee' => $this->fee,
        ];

        return $shop;
    }
}
