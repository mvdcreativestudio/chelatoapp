<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCashRegisterLogRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cash_register_id' => 'required|integer',
            'open_time' => 'required|date',
            'close_time' => 'nullable|date',
            'cash_sales' => 'required|integer',
            'pos_sales' => 'required|integer',
            'cash_float' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'cash_register_id.required' => 'El identificador de la caja registradora es obligatorio',
            'open_time.required' => 'El horario de apertura de la caja registradora es obligatorio',
            'close_time.required' => 'El horario de cierre de la caja registradora es obligatorio',
            'cash_sales.required' => 'El dinero en ventas en efectivo de la caja registradora es obligatorio',
            'pos_sales.required' => 'El dinero en ventas por POS de la caja registradora es obligatorio',
            'cash_float.required' => 'El fondo de caja de la caja registradora es obligatorio'
        ];
    }
}
