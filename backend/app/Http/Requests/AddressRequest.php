<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
   public function authorize(): bool
   {
      return true;
   }

   public function rules(): array
   {
      $address = $this->route('address');
      $userId = Auth::id();
      
      return [
         'pickup_id' => [
            'sometimes',
            'integer',
            'exists:pickups,id',
            Rule::unique('addresses')
               ->where('user_id', $userId)
               ->ignore($address)
         ],
         'address' => [
            'nullable',
            'string',
            'max:1000',
            Rule::unique('addresses')
               ->where('user_id', $userId)
               ->whereNotNull('address')
               ->ignore($address)
         ],
         'is_default' => 'sometimes|boolean',
      ];
   }
   
   public function messages(): array
   {
      return [
         'pickup_id.unique' => 'You already have an address with this pickup point',
         'address.unique' => 'This address already exists in your address list',
      ];
   }
   
   public function prepareForValidation()
   {
      if ($this->has('address') && trim($this->address) === '') {
         $this->merge(['address' => null]);
      }
   }
}
