<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'El identificador del producto es obligatorio.',
            'id.exists' => 'El producto no existe.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser al menos 1.'
        ];
    }
}
