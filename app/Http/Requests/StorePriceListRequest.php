<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id'    => 'required|integer|exists:stores,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'currency'    => 'required|string|in:Peso,Dólar',
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'La tienda es obligatoria.',
            'store_id.exists'   => 'La tienda seleccionada no es válida.',
            'name.required'     => 'El nombre de la lista es obligatorio.',
            'currency.required' => 'La moneda es obligatoria.',
            'currency.in'       => 'La moneda debe ser Peso o Dólar.',
        ];
    }
}
