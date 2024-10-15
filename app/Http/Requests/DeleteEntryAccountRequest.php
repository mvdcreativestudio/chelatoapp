<?php

namespace App\Http\Requests;

use App\Models\EntryAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DeleteEntryAccountRequest extends FormRequest
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
            'ids.*' => ['exists:entry_accounts,id'], // Validar que las cuentas contables existen
        ];
    }

    /**
     * Realiza validaciones adicionales después de que las reglas básicas hayan pasado.
     */
    protected function passedValidation()
    {
        $entryAccountIds = $this->input('ids', []); // IDs para eliminación múltiple

        // Si no hay un array, revisar el ID único de la ruta
        if (!$entryAccountIds && $this->route('entry_account')) {
            $entryAccountIds = [$this->route('entry_account')];
        }

        foreach ($entryAccountIds as $entryAccountId) {
            $entryAccount = EntryAccount::find($entryAccountId);

            // Verificar si la cuenta contable tiene detalles asociados
            if ($entryAccount && $entryAccount->entryDetails()->exists()) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => "La cuenta contable '{$entryAccount->name}' tiene detalles de asientos asociados y no se puede eliminar."
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
