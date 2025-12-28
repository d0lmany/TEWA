<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total' => (float) $this->total,
            'is_hidden' => (bool) $this->is_hidden,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'destination' => new AddressResource($this->whenLoaded('address')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            
            'locations' => $this->whenLoaded('locations', function () {
                return $this->locations->map(function ($location) {
                    return [
                        'id' => $location->id,
                        'location_type' => $location->location_type,
                        'location_id' => $location->location_id,
                        'notes' => $location->notes,
                        'arrived_at' => $location->arrived_at,
                        'left_at' => $location->left_at,
                        'duration' => $location->left_at 
                            ? $location->arrived_at->diffInHours($location->left_at) . ' hours'
                            : null,
                        'is_current' => is_null($location->left_at),
                    ];
                });
            }),
            'current_location' => $this->whenLoaded('locations', function () {
                return $this->locations
                    ->whereNull('left_at')
                    ->first();
            }),
            
            'status_history' => $this->whenLoaded('statusHistory', function () {
                return $this->statusHistory->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'old_status' => $history->old_status,
                        'new_status' => $history->new_status,
                        'changed_by_id' => $history->changed_by_id,
                        'notes' => $history->notes,
                        'created_at' => $history->created_at,
                    ];
                });
            }),
            'last_status_change' => $this->whenLoaded('statusHistory', function () {
                return $this->statusHistory->last();
            }),
        ];
    }
}
