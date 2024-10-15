<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryDetailRequest extends FormRequest
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
            'entry_id' => ['required', 'exists:entries,id'],
            'entry_account_id' => ['required', 'exists:entry_accounts,id'],
            'amount_debit' => ['required_without:amount_credit', 'numeric', 'min:0'],
            'amount_credit' => ['required_without:amount_debit', 'numeric', 'min:0'],
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
            'entry_id.required' => 'El asiento es obligatorio.',
            'entry_id.exists' => 'El asiento seleccionado no es válido.',
            'entry_account_id.required' => 'La cuenta contable es obligatoria.',
            'entry_account_id.exists' => 'La cuenta contable seleccionada no es válida.',
            'amount_debit.required_without' => 'El monto del Debe es obligatorio.',
            'amount_debit.numeric' => 'El monto del Debe debe ser un número.',
            'amount_debit.min' => 'El monto del Debe no puede ser negativo.',
            'amount_credit.required_without' => 'El monto del Haber es obligatorio.',
            'amount_credit.numeric' => 'El monto del Haber debe ser un número.',
            'amount_credit.min' => 'El monto del Haber no puede ser negativo.',
        ];
    }
}
