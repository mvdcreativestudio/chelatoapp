<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CurrentAccount;

class StoreCurrentAccountRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convertimos los campos booleanos de texto a booleanos reales
        $this->merge([
            'partial_payment' => filter_var($this->partial_payment, FILTER_VALIDATE_BOOLEAN),
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
            'total_debit' => 'required|numeric|min:0',
            'client_id' => 'nullable|integer|exists:clients,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'current_account_settings_id' => 'required|integer|exists:current_account_settings,id',
            'partial_payment' => 'required|boolean',
            'description' => 'nullable|string',
            'amount_paid' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    // Si es un pago parcial, el monto pagado no puede ser mayor que el total
                    if ($this->partial_payment && $value > $this->total_debit) {
                        $fail('El monto pagado no puede ser mayor que el monto total.');
                    }

                    if ($this->partial_payment && $value == $this->total_debit) {
                        $fail('El monto parcial no puede ser igual al monto total.');
                    }
                },
            ],
            'payment_method_id' => [
                'nullable',
                'integer',
                'exists:payment_methods,id',
                function ($attribute, $value, $fail) {
                    // Si se ha indicado que hay pago parcial o la cuenta está pagada, debe seleccionarse un método de pago
                    if ($this->partial_payment && empty($value)) {
                        $fail('Debe seleccionar un método de pago cuando se registra un pago parcial o total.');
                    }
                },
            ],
            'currency_id_current_account' => 'required|integer|exists:currencies,id',
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
            'total_debit.required' => 'El monto total es obligatorio.',
            'total_debit.numeric' => 'El monto total debe ser un número.',
            'total_debit.min' => 'El monto total debe ser un valor positivo.',
            'client_id.exists' => 'El cliente seleccionado no es válido.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
            'current_account_settings_id.required' => 'El tipo de crédito es obligatorio.',
            'current_account_settings_id.exists' => 'El tipo de crédito seleccionado no es válido.',
            'partial_payment.required' => 'Debe especificar si es un pago parcial.',
            'partial_payment.boolean' => 'El valor de pago parcial debe ser verdadero o falso.',
            'description.string' => 'La descripción debe ser un texto.',
            'amount_paid.numeric' => 'El monto pagado debe ser un número.',
            'amount_paid.min' => 'El monto pagado debe ser un valor positivo.',
            'payment_method_id.integer' => 'El método de pago debe ser un número entero.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido.',
            'currency_id_current_account.required' => 'Debe seleccionar una moneda para la cuenta corriente.',
            'currency_id_current_account.exists' => 'La moneda seleccionada no es válida.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar que solo se haya enviado client_id o supplier_id, no ambos
            if ($this->client_id && $this->supplier_id) {
                $validator->errors()->add('client_id', 'No puede seleccionar un cliente y un proveedor al mismo tiempo.');
                $validator->errors()->add('supplier_id', 'No puede seleccionar un proveedor y un cliente al mismo tiempo.');
            }

            // Verificar que al menos uno de los dos esté presente
            if (!$this->client_id && !$this->supplier_id) {
                $validator->errors()->add('client_id', 'Debe seleccionar un cliente o un proveedor.');
                $validator->errors()->add('supplier_id', 'Debe seleccionar un proveedor o un cliente.');
            }

            // Verificar si ya existe una cuenta corriente para este cliente con la misma moneda
            if ($this->client_id) {
                $exists = CurrentAccount::where('client_id', $this->client_id)
                    ->where('currency_id', $this->currency_id_current_account)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('client_id', 'Ya existe una cuenta corriente para este cliente con la misma moneda.');
                } else {
                    // Verificar si ya tiene más de dos cuentas con monedas diferentes
                    $count = CurrentAccount::where('client_id', $this->client_id)->count();
                    if ($count >= 2) {
                        $validator->errors()->add('client_id', 'No se puede crear más de dos cuentas corrientes con distintas monedas para este cliente.');
                    }
                }
            }

            // Verificar si ya existe una cuenta corriente para este proveedor con la misma moneda
            if ($this->supplier_id) {
                $exists = CurrentAccount::where('supplier_id', $this->supplier_id)
                    ->where('currency_id', $this->currency_id_current_account)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('supplier_id', 'Ya existe una cuenta corriente para este proveedor con la misma moneda.');
                } else {
                    // Verificar si ya tiene más de dos cuentas con monedas diferentes
                    $count = CurrentAccount::where('supplier_id', $this->supplier_id)->count();
                    if ($count >= 2) {
                        $validator->errors()->add('supplier_id', 'No se puede crear más de dos cuentas corrientes con distintas monedas para este proveedor.');
                    }
                }
            }
        });
    }
}