<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordUserRequest extends FormRequest
{
   public function authorize(): bool
   {
      return true;
   }

   public function rules(): array
   {
      return [
         'password' => [
            'required', 'string', 
            'confirmed',
            Password::min(8)
               ->letters()
               ->mixedCase()
               ->numbers()
               ->symbols()
               ->max(100)
            ],
         'old_password' => [
            'required', 'string', 
            Password::min(8)
               ->letters()
               ->mixedCase()
               ->numbers()
               ->symbols()
               ->max(100)
            ],
      ];
   }
}
