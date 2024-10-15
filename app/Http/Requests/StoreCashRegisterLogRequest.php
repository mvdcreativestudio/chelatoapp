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
            'cash_float' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'cash_register_id.required' => 'El identificador de la caja registradora es obligatorio',
            'cash_float.required' => 'El fondo de caja de la caja registradora es obligatorio'
        ];
    }
}
