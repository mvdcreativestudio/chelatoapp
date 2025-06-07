<?php

namespace App\Http\Requests;

use App\Models\CurrentAccountSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DeleteCurrentAccountSettingsRequest extends FormRequest
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
            'ids' => ['nullable', 'array'], // Para eliminación múltiple
            'ids.*' => ['exists:current_account_settings,id'], // Validar que las configuraciones existen
        ];
    }

    /**
     * Realiza validaciones adicionales después de que las reglas básicas hayan pasado.
     */
    protected function passedValidation()
    {
        $currentAccountSettingsIds = $this->input('ids', []); // IDs para eliminación múltiple

        // Si no hay un array, revisar el ID único de la ruta
        if (!$currentAccountSettingsIds && $this->route('current_account_setting')) {
            $currentAccountSettingsIds = [$this->route('current_account_setting')];
        }

        foreach ($currentAccountSettingsIds as $currentAccountSettingsId) {
            $currentAccountSetting = CurrentAccountSettings::find($currentAccountSettingsId);

            // Verificar si la configuración de cuenta corriente tiene créditos iniciales asociados
            if ($currentAccountSetting && $currentAccountSetting->initialCredits()->exists()) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => "La configuración de cuenta corriente '{$currentAccountSetting->id}' tiene créditos iniciales asociados y no se puede eliminar."
                    ], 400)
                );
            }
        }
    }

    /**
     * Maneja la validación fallida.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $validator->errors()
            ], 400)
        );
    }
}
