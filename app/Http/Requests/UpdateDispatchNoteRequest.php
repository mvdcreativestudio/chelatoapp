<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDispatchNoteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
            'product_id' => 'required|integer|exists:products,id',
            'date' => 'required|date',
            'quantity' => 'required|integer',
            'bombing_type' => 'required|string|max:255',
            'delivery_method' => 'required|string|max:255',
        ];
    }
}