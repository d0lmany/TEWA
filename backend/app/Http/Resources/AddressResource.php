<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'pickup' => $this->whenLoaded('pickup', $this->pickup),
         'pickup_id' => $this->pickup,
         'address' => $this->when($this->address, $this->address),
         'is_default' => $this->is_default
      ];
   }
}
