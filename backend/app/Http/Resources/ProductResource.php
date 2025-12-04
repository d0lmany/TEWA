<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ProductResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'name' => $this->name,
         'quantity' => $this->quantity,
         'photo' => url('storage/products/' . $this->photo),
         'category' => new CategoryResource($this->whenLoaded('category')),
         'price' => [
            'discount' => floatval($this->discount),
            'base_price' => floatval($this->base_price),
            'final_price' => floatval($this->final_price),
         ],
         'tags' => $this->tags,
         'feedbacks' => [
            'rating' => $this->rating,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
         ],
         'details' => new ProductDetailResource($this->whenLoaded('productDetail')),
         'attributes' => $this->when(
            $this->relationLoaded('attributes') && $this->attributes->isNotEmpty(),
            fn() => $this->attributes
               ->groupBy('attr_key')
               ->map(fn($group) => $group->map(fn($item) => Arr::except($item->toArray(), ['attr_key', 'product_id'])))
               ->toArray()
         ),
         'shop' => new ShopResource($this->whenLoaded('shop')),
         'status' => $this->status,
      ];
   }
}
