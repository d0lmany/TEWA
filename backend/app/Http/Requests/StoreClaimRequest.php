<?php

namespace App\Http\Requests;

use App\Http\Controllers\API\ConfigController;
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
        $conf = ConfigController::getConfig();
        $isShop = $conf['mode'] === 'shop';
        return [
            'entity' => ['required', 'string', Rule::in(['product', 'shop'])],
            'entity_id' => ['required', 'integer', 'min:'. ($isShop ? '0' : '1')],
            'topic' => ['required', 'string', 'min:5', 'max:255'],
            'text' => ['required', 'string', 'min:15']
        ];
    }
}
