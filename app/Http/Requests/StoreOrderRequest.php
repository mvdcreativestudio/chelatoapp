<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        // Se desactivó por el cambio en PDV guarde en 'orders', desactivar en caso de que tenga eCommerce.
          'name' => 'nullable|max:255',
          'lastname' => 'nullable|max:255',
          'address' => 'required',
          'phone' => 'required',
          'email' => 'required|email',
          'payment_method' => 'required',
          'client_id' => 'nullable|integer|exists:clients,id',
          'doc_type' => 'nullable|integer',
          'document' => 'nullable|string|max:32',
          'client_type' => 'nullable|string|in:company,individual,no-client',
        ];
    }
}
