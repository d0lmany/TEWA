<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFavoriteListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'not_in:__favorite__']
        ];
    }

    public function messages()
    {
        return [
            'name.not_in' => 'It\'s a reserved word'
        ];
    }
}
