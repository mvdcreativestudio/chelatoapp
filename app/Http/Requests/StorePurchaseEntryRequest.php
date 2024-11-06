<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseEntryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => 'nullable|exists:products,id',
            'raw_material_id' => 'nullable|exists:raw_materials,id',
            'quantity' => 'required|integer|min:1',
            'entry_date' => 'required|date',
            'purchase_order_items_id' => 'required|integer|exists:purchase_order_items,id',
        ];
    }
}
