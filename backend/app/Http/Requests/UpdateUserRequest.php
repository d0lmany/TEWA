<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
   public function authorize(): bool
   {
      return true;
   }

   public function rules(): array
   {
      return [
         'name' => ['sometimes', 'string', 'max:255'],
         'birthday' => ['sometimes', 'date', 'before:-13 years'],
         'picture' => [
            'sometimes', 'image',
            'mimes:jpeg,png,jpg,webp', 'max:2048',
         ],
         'delete_picture' => ['sometimes', 'boolean']
      ];
   }
}
