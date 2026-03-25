<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashRegisterLogRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cash_register_id' => 'required|integer',
            'cash_float' => 'required|numeric',
            'name' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'cash_register_id.required' => 'El identificador de la caja registradora es obligatorio',
            'cash_float.required' => 'El fondo de caja de la caja registradora es obligatorio',
            'name.required' => 'El nombre de la apertura de caja es obligatorio',
        ];
    }
}
