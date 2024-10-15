<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'slug' => 'required|string|exists:stores,slug'
        ];
    }

    public function messages()
    {
        return [
            'slug.required' => 'El identificador de la tienda es obligatorio.',
            'slug.string' => 'El identificador de la tienda debe ser un slug valido.',
            'slug.exists' => 'La tienda seleccionada no existe.'
        ];
    }
}
