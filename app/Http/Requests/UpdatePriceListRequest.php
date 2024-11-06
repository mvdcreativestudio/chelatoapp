<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePriceListRequest extends FormRequest
{
    public function failedValidation(Validator $validator)
    {
        // Esto hará que el mensaje de error se muestre directamente en pantalla
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

    public function rules()
    {
        return [
            'store_id' => 'sometimes|exists:stores,id', // Requiere que el store_id exista en la tabla stores
            'name' => 'sometimes|string|max:255', // Nombre obligatorio, tipo string y longitud máxima de 255
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
