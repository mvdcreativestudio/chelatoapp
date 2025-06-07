<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRawMaterialPriceRequest extends FormRequest
{
    public function authorize()
    {
        return true;  // Ajusta según tus necesidades de autorización
    }

    public function rules()
    {
        return [
            'raw_material_id' => 'required|exists:raw_materials,id',
            'currency' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'raw_material_id.required' => 'El campo de materia prima es obligatorio.',
            'raw_material_id.exists' => 'La materia prima seleccionada no es válida.',
            'currency.required' => 'El campo de moneda es obligatorio.',
            'currency.string' => 'El campo de moneda debe ser una cadena de texto.',
            'currency.max' => 'La moneda no puede exceder los 255 caracteres.',
            'price.required' => 'El campo de precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio debe ser un valor positivo.',
        ];
    }
}
