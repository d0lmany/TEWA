<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity' => ['required', 'string', Rule::in(['product', 'shop', 'review', 'pickup'])],
            'entity_id' => ['required', 'integer', 'min:1'],
            'topic' => ['required', 'string', 'max:255'],
            'text' => ['required', 'string']
        ];
    }
}
