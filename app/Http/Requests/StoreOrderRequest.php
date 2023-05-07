<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'products' => ['array', 'required', 'min:1'],
            'products.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'products.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }
}
