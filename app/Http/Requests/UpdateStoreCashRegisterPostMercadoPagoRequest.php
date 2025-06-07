<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreCashRegisterPostMercadoPagoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'store_id' => 'required|integer|exists:stores,id',
        ];
    }

    public function messages()
    {
        return [
            'name.string' => 'El nombre debe ser un texto válido.',
            'store_id.required' => 'El identificador de la tienda es obligatorio.',
            'store_id.exists' => 'La tienda proporcionada no existe.',
        ];
    }
}
