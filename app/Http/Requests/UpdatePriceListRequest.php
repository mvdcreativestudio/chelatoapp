<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'currency'            => 'required|string|in:Peso,Dólar',
            'prices'              => 'nullable|array',
            'prices.*'            => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre de la lista es obligatorio.',
            'currency.required' => 'La moneda es obligatoria.',
            'currency.in'       => 'La moneda debe ser Peso o Dólar.',
        ];
    }
}
