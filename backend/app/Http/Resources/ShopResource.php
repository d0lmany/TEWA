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
         'picture' => $this->picture ? url('storage/shops/' . $this->picture) : null,
         'description' => $this->description,
         'seller' => $this->when($this->relationLoaded('seller') && $this->seller, function() {
            return new SellerResource($this->seller);
         }),
         'rating' => $this->when(isset($this->rating), $this->rating),
      ];

      return $shop;
   }
}
