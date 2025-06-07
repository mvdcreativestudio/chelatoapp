<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutStoreOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'name' => 'required|max:255',
          'lastname' => 'required|max:255',
          'address' => 'nullable',
          'phone' => 'required',
          'email' => 'required|email',
          'payment_method' => 'required',
          'estimate_id' => 'sometimes|nullable',
          'shipping_method' => 'required',
          'shipping_cost' => 'required|numeric|min:0',
        ];
    }
}
