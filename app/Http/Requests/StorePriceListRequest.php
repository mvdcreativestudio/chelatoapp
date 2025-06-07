<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceListRequest extends FormRequest
{
    public function rules()
    {
        return [
            'store_id' => 'required|exists:stores,id', // Asegura que el store_id exista en la tabla stores
            'name' => 'required|string|max:255', // Nombre obligatorio, tipo string y longitud máxima de 255
            'description' => 'nullable|string|max:1000', // Descripción opcional, tipo string y longitud máxima de 1000
        ];
    }

    public function messages()
    {
        return [
            'store_id.required' => 'El campo "TIENDA" es obligatorio.',
            'store_id.exists' => 'La tienda seleccionada no es válida.',
            'name.required' => 'El campo "NOMBRE" es obligatorio.',
            'name.max' => 'El campo "NOMBRE" no debe exceder los 255 caracteres.',
            'description.max' => 'El campo "DESCRIPCIÓN" no debe exceder los 1000 caracteres.',
        ];
    }
}
