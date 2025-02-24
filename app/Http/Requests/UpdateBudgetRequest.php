<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateBudgetRequest extends FormRequest
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
            'store_id' => 'required|exists:stores,id',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
            'discount_type' => 'nullable|in:Percentage,Fixed',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,pending_approval,sent,negotiation,approved,rejected,expired,cancelled',
            'is_blocked' => 'boolean',
            'products' => 'required|array',
            'products.*' => 'exists:products,id',
            'items' => 'required'
        ];
    }

    protected function prepareForValidation()
    {
        // Log datos recibidos para debugging
        Log::info('Datos recibidos en UpdateBudgetRequest:', $this->all());

        // Procesar items
        $items = $this->items;
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        // Procesar total
        $total = str_replace(['$', ','], '', $this->total);
        
        $this->merge([
            'items' => $items,
            'total' => floatval($total),
            'is_blocked' => $this->boolean('is_blocked')
        ]);

        // Log datos procesados para debugging
        Log::info('Datos procesados en UpdateBudgetRequest:', $this->all());
    }
}