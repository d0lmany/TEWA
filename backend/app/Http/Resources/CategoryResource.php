<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = [
            'id' => $this->id,
            'name' => $this->name,
            'parent' => $this->when($this->relationLoaded('parent') && $this->parent, function () {
                return [
                    'id' => $this->parent->id,
                    'name' => $this->parent->name,
                ];
            }),
        ];
        return $category;
    }
}
