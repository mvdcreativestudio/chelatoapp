<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    // Autoriza el uso del request para todos los usuarios
    public function authorize()
    {
        return true;
    }

    public function validationData()
  {
    // Incluir el `productId` de la URL en los datos de validación
    $data = $this->all();
    $data['productId'] = $this->route('productId');
    return $data;
  }

    // Define las reglas de validación
    public function rules()
    {
        return [
            'productId' => 'required|integer|exists:products,id',
            'flavors' => 'array',
            'flavors.*' => 'integer|exists:flavors,id'
        ];
    }

    // Opcional: Personaliza los mensajes de error
    public function messages()
    {
        return [
            'productId.required' => 'El producto es obligatorio.',
            'productId.exists' => 'El producto seleccionado no existe.',
            'flavors.array' => 'Los variaciones deben ser una lista.',
            'flavors.*.exists' => 'Algunos variaciones seleccionados no existen.'
        ];
    }
}
