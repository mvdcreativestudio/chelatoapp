<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCashRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'store_id' => 'required|integer',
            'user_id' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'store_id.required' => 'El identificador de la tienda es obligatorio',
            'user_id.required' => 'El identificador de la tienda es obligatorio'
        ];
    }
}
