<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
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
            'entry_date' => ['required', 'date'],
            'entry_type_id' => ['required', 'exists:entry_types,id'],
            'concept' => ['required', 'string', 'max:255'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'details' => ['required', 'array'],
            'details.*.entry_account_id' => ['required', 'exists:entry_accounts,id'],
            'details.*.amount_debit' => ['required_without:details.*.amount_credit', 'numeric', 'min:0'],
            'details.*.amount_credit' => ['required_without:details.*.amount_debit', 'numeric', 'min:0'],
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
            'entry_date.required' => 'La fecha del asiento es obligatoria.',
            'entry_date.date' => 'La fecha del asiento debe ser una fecha válida.',
            'entry_type_id.required' => 'El tipo de asiento es obligatorio.',
            'entry_type_id.exists' => 'El tipo de asiento seleccionado no es válido.',
            'concept.required' => 'El concepto del asiento es obligatorio.',
            'concept.string' => 'El concepto del asiento debe ser una cadena de texto.',
            'concept.max' => 'El concepto del asiento no puede tener más de :max caracteres.',
            'currency_id.required' => 'La moneda del asiento es obligatoria.',
            'currency_id.exists' => 'La moneda seleccionada no es válida.',
            'details.required' => 'Los detalles del asiento son obligatorios.',
            'details.array' => 'Los detalles del asiento deben ser un arreglo.',
            'details.*.entry_account_id.required' => 'La cuenta contable del detalle es obligatoria.',
            'details.*.entry_account_id.exists' => 'La cuenta contable seleccionada no es válida.',
            'details.*.amount_debit.required_without' => 'El monto del Debe es obligatorio.',
            'details.*.amount_debit.numeric' => 'El monto del Debe debe ser un número.',
            'details.*.amount_debit.min' => 'El monto del Debe no puede ser negativo.',
            'details.*.amount_credit.required_without' => 'El monto del Haber es obligatorio.',
            'details.*.amount_credit.numeric' => 'El monto del Haber debe ser un número.',
            'details.*.amount_credit.min' => 'El monto del Haber no puede ser negativo.',
        ];
    }
}
