<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'full_name' => $this->full_name,
            'code' => $this->code,
            'type' => $this->type,
            'shops' => ShopResource::collection($this->whenLoaded('shops')),
            'verified_at' => $this->verified_at,
        ];
    }
}
