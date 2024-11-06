<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;  
    }

    public function rules()
    {
        return [
            'purchase_orders_id' => 'required|exists:purchase_orders,id',
            'raw_material_id' => 'nullable|exists:raw_materials,id',
            'product_id' => 'nullable|integer',
            'quantity' => 'required|integer|min:1',
            'currency' => 'nullable',
            'unit_price' => 'required|numeric|min:0',
        ];
    }
}