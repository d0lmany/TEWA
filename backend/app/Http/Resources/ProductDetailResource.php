<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'product_id' => $this->product_id,
         'album' => $this->album 
            ? array_map(
               fn($path) => url('storage/products/' . $path), 
               $this->album ?? []
               )
            : [],
         'description' => $this->description,
         'application' => $this->application,
      ];
   }
}
