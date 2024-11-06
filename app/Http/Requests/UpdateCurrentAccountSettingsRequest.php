<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\CurrentAccounts\TransactionTypeEnum;
use App\Models\CurrentAccountInitialCredit;

class UpdateCurrentAccountSettingsRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Modifica según las políticas de autorización que estés usando
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'transaction_type' => ['required', Rule::in(TransactionTypeEnum::getValues())],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Mensajes de error personalizados para las reglas de validación.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'transaction_type.required' => 'El tipo de transacción es obligatorio.',
            'transaction_type.in' => 'El tipo de transacción seleccionado no es válido.',
            'late_fee.numeric' => 'La tasa de mora debe ser un valor numérico.',
            'late_fee.min' => 'La tasa de mora no puede ser negativa.',
            'payment_terms.integer' => 'Los términos de pago deben ser un número entero.',
            'payment_terms.min' => 'Los términos de pago no pueden ser negativos.',
        ];
    }

    public function withValidator($validator)
    {
        $currentAccountSettingsId = $this->route('current_account_setting');
        $validator->after(function ($validator) use ($currentAccountSettingsId) {
                $existsInCurrentAccounts = CurrentAccountInitialCredit::where('current_account_settings_id', $currentAccountSettingsId)
                    ->exists();
                if ($existsInCurrentAccounts) {
                    $validator->errors()->add('current_account_initial_credit_id', 'No se puede actualizar este configuración de cuenta corriente porque tiene registros asociados.');
                }
        });
    }
}