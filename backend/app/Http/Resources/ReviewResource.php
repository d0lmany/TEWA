<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $review = [
            'id' => $this->id,
            'user' => $this->when($this->relationLoaded('user') && $this->user, function () {
                return new UserResource($this->user);
            }),
            'text' => $this->text,
            'evaluation' => $this->evaluation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $review;
    }
}
