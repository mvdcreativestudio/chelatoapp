<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_paid' => filter_var($this->is_paid, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'expense_category_id' => 'required|integer|exists:expense_categories,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'store_id' => 'nullable|integer|exists:stores,id',
            'is_paid' => 'required|boolean',
            'amount_paid' => [
                'nullable',
                'numeric',
                // function ($attribute, $value, $fail) {
                //     if ($this->is_paid && $value != $this->amount) {
                //         $fail('El monto pagado debe ser igual al monto del gasto.');
                //     }
                // },
            ],
            'payment_method_id' => [
                'nullable', // Permite que sea nulo si no se selecciona
                'integer', // Asegura que sea un número entero si se proporciona
                'exists:payment_methods,id',
                'required_if:is_paid,true', // Requerido solo si is_paid es true
            ],
            'concept' => 'nullable|string',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'El campo monto es requerido.',
            'amount.numeric' => 'El campo monto debe ser un número.',
            'due_date.required' => 'El campo fecha de vencimiento es requerido.',
            'due_date.date' => 'El campo fecha de vencimiento debe ser una fecha válida.',
            'supplier_id.required' => 'El campo proveedor es requerido.',
            'supplier_id.integer' => 'El campo proveedor debe ser un número entero.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
            'expense_category_id.required' => 'El campo categoría de gasto es requerido.',
            'expense_category_id.integer' => 'El campo categoría de gasto debe ser un número entero.',
            'expense_category_id.exists' => 'La categoría de gasto seleccionada no es válida.',
            'currency_id.required' => 'El campo moneda es requerido.',
            'currency_id.integer' => 'El campo moneda debe ser un número entero.',
            'currency_id.exists' => 'La moneda seleccionada no es válida.',
            'store_id.integer' => 'El campo tienda debe ser un número entero.',
            'store_id.exists' => 'La tienda seleccionada no es válida.',
            'is_paid.required' => 'Debe indicar si el gasto está pagado o no.',
            'is_paid.in' => 'El campo de pago debe ser verdadero o falso.',
            'amount_paid.numeric' => 'El campo monto pagado debe ser un número.',
            'payment_method_id.integer' => 'El método de pago debe ser un número entero.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido.',
            'payment_method_id.required_if' => 'Debe seleccionar un método de pago cuando el gasto está marcado como pagado.',
            'concept.string' => 'El concepto debe ser una cadena de texto.',
        ];
    }
}
