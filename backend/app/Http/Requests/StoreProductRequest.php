<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'min:0'],
            'photo' => ['required', 'file', 'image',
                'mimes:jpeg,png,jpg,webp', 'max:5120',
                'mimetypes:image/jpeg,image/png,image/jpg,image/webp'],
            'category_id' => ['required', 'numeric', 'exists:categories,id'],
            'shop_id' => ['required', 'numeric', 'exists:shops,id'],
        ];
    }
}
