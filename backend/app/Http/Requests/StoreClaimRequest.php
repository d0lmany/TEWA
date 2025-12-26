<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'entity' => ['required', 'string', Rule::in(['product', 'shop'])],
            'entity_id' => ['required', 'integer', 'min:1'],
            'topic' => ['required', 'string', 'min:5', 'max:255'],
            'text' => ['required', 'string', 'min:15']
        ];
    }
}
