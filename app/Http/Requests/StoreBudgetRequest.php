<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
            'lead_id' => 'nullable|exists:leads,id',
            'order_id' => 'nullable|exists:orders,id',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'store_id' => 'nullable|exists:stores,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'total' => 'required|numeric',
            'discount_type' => 'nullable|in:Percentage,Fixed',
            'discount' => 'nullable|numeric',
            'is_blocked' => 'boolean',
        ];
    }
}