<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'income_name' => ['required', 'string', 'max:255'],
            'income_description' => ['nullable', 'string', 'max:255'],
            'income_date' => ['required', 'date'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'income_category_id' => ['nullable', 'exists:income_categories,id'],
            'currency' => ['required', 'string'],
            'exchange_rate' => ['required', 'numeric'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'items' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'income_name.required' => 'El nombre del ingreso es obligatorio.',
            'income_name.string' => 'El nombre del ingreso debe ser una cadena de texto.',
            'income_name.max' => 'El nombre del ingreso no puede tener más de :max caracteres.',
            'income_description.string' => 'La descripción del ingreso debe ser una cadena de texto.',
            'income_description.max' => 'La descripción del ingreso no puede tener más de :max caracteres.',
            'income_date.required' => 'La fecha del ingreso es obligatoria.',
            'income_date.date' => 'La fecha del ingreso debe ser una fecha válida.',
            'payment_method_id.required' => 'El método de pago es obligatorio.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido.',
            'income_category_id.exists' => 'La categoría seleccionada no es válida.',
            'currency.required' => 'La moneda del ingreso es obligatoria.',
            'currency.string' => 'La moneda debe ser una cadena de texto.',
            'exchange_rate.required' => 'La tasa de cambio es obligatoria.',
            'exchange_rate.numeric' => 'La tasa de cambio debe ser un número.',
            'tax_rate_id.exists' => 'La tasa de impuesto seleccionada no es válida.',
            'client_id.exists' => 'El cliente seleccionado no es válido.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
            'items.required' => 'Los productos del ingreso son obligatorios.',
        ];
    }
}