<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'income_name' => ['required', 'string', 'max:255'],
            'income_description' => ['nullable', 'string', 'max:255'],
            'income_date' => ['required', 'date'],
            'income_amount' => ['required', 'numeric'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'income_category_id' => ['required', 'exists:income_categories,id'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'client_id' => ['nullable', 'exists:clients,id'], // Es nullable ya que es opcional
            'supplier_id' => ['nullable', 'exists:suppliers,id'], // Es nullable ya que es opcional
        ];
    }

    /**
     * Obtiene los mensajes de validación personalizados.
     *
     * @return array
     */
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
            'income_amount.required' => 'El monto del ingreso es obligatorio.',
            'income_amount.numeric' => 'El monto del ingreso debe ser un número.',
            'payment_method_id.required' => 'El método de pago es obligatorio.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido.',
            'income_category_id.required' => 'La categoría del ingreso es obligatoria.',
            'income_category_id.exists' => 'La categoría seleccionada no es válida.',
            'currency_id.required' => 'La moneda del ingreso es obligatoria.',
            'currency_id.exists' => 'La moneda seleccionada no es válida.',
            'client_id.exists' => 'El cliente seleccionado no es válido.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
        ];
    }
}
