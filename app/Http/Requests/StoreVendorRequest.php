<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'lastname.required' => 'El apellido es obligatorio',
            'email.email' => 'El correo electrónico debe ser una dirección válida',
            'user_id' => 'El usuario debe existir en la base de datos',
            'user_id.exists' => 'El usuario seleccionado no existe',
        ];
    }
}
