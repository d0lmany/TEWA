<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => [
                'required', 
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'birthday' => ['required', 'date', 'before:-13 years']
        ];
    }

    public function messages(): array
    {
        return [
            'birthday.before' => 'You must be at least 13 years old.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
