<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePosOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => 'required|date',
            'hour' => 'required|time',
            'cash_register_log_id' => 'required|int',
            'cash_sales' => 'required|int',
            'pos_sales' => 'required|int',
            'discount' => 'required|numeric',
            'client_type' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'El fecha de la orden es obligatoria',
            'hour.required' => 'El hora de la orden es obligatoria',
            'cash_register_log_id.required' => 'El identificador del LOG de la caja registradora es obligatorio',
            'cash_sales.required' => 'La cantidad de dinero en efectivo es obligatoria.',
            'pos_sales.required' => 'La cantidad de dinero del POS es obligatoria.',
            'discount.required' => 'La cantidad de descuento es obligatoria.',
        ];
    }
}
