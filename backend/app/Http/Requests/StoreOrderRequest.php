<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_pickup_id' => ['sometimes', 'integer', 'min:1'],
            'destination_address_id' => ['sometimes', 'integer', 'min:1'],
            'is_hidden' => ['sometimes', 'boolean'],
            'cart_items' => ['required', 'array', 'min:1'],
            'cart_items.*' => ['integer'/*, 'exists:cart_items,id'*/],
        ];
    }
}
