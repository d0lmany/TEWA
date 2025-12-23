<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $albumUrls = collect($this->album ?? [])
            ->map(fn($path) => url('storage/' . $path))
            ->toArray();

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'album' => $albumUrls,
            'description' => $this->description,
            'application' => $this->application,
        ];
    }
}
