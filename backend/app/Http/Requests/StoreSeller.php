<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeller extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:8', 'max:300'],
            'code' => ['required', 'string', 'min:12', 'max:15'],
            'payment_account' => ['required', 'string', 'min:10'],
            'passport_numbers' => ['required', 'string', 'min:11', 'max:11'],
            'passport_scan' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'type' => ['required', 'string', Rule::in(['LLC', 'self_employed', 'individual'])],
        ];
    }
}
