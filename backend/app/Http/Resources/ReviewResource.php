<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        $review = [
            'id' => $this->id,
            'user' => $this->when($this->relationLoaded('user') && $this->user, function () {
                return $this->user;
            }),
            'text' => $this->text,
            'evaluation' => $this->evaluation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $review;
    }
}
