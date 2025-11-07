<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:300'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'base_price' => ['sometimes', 'numeric', 'max:9999999999,99', 'min:0'],
            'photo' => ['sometimes', 'file', 'image',
                'mimes:jpeg,png,jpg,webp', 'max:5120',
                'mimetypes:image/jpeg,image/png,image/jpg,image/webp'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'discount' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'status' => ['sometimes', 'string', Rule::in(['on', 'off', 'draft'])],
        ];
    }
}
