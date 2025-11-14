<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:300'],
            'quantity' => ['required', 'integer', 'min:0'],
            'base_price' => ['required', 'numeric', 'max:9999999999,99', 'min:0'],
            'photo' => [
                'required', 'image',
                'mimes:jpeg,png,jpg,webp', 'max:5120',
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'discount' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'status' => ['sometimes', 'string', Rule::in(['on', 'off', 'draft'])],
            'shop_id' => ['required', 'exists:shops,id'],
        ];
    }
}
