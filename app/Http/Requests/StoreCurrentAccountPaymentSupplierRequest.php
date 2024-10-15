<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CurrentAccount;

class StoreCurrentAccountPaymentSupplierRequest extends FormRequest
{
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
            'current_account_id' => 'required|integer|exists:current_accounts,id', // Validar que el ID de cuenta corriente sea válido
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'payment_amount' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    // Obtener la cuenta corriente usando el current_account_id enviado en el request
                    $currentAccount = CurrentAccount::find($this->current_account_id);

                    if ($currentAccount) {
                        $totalPaid = $currentAccount->payments->sum('payment_amount'); // Suma de los pagos existentes
                        $totalDebit = $currentAccount->total_debit; // Monto total que se debe

                        // Valida que el nuevo pago no exceda el total pendiente
                        if ($totalPaid + $value > $totalDebit) {
                            $fail('El monto total pagado no puede exceder el monto total de la cuenta corriente.');
                        }
                    } else {
                        $fail('La cuenta corriente seleccionada no es válida.');
                    }
                },
            ],
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'payment_date' => 'required|date',
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
            'current_account_id.required' => 'La cuenta corriente es obligatoria.',
            'current_account_id.exists' => 'La cuenta corriente seleccionada no es válida.',
            'supplier_id.required' => 'El proveedor es obligatorio.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
            'payment_amount.required' => 'El monto pagado es obligatorio.',
            'payment_amount.numeric' => 'El monto pagado debe ser un número.',
            'payment_amount.min' => 'El monto pagado debe ser un valor positivo.',
            'payment_method_id.required' => 'El método de pago es obligatorio.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido.',
            'payment_date.required' => 'La fecha de pago es obligatoria.',
            'payment_date.date' => 'La fecha de pago no es válida.',
        ];
    }
}
