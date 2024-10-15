<?php

namespace App\Http\Requests;

use App\Models\EntryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DeleteEntryTypeRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Puedes poner lógica de autorización aquí si es necesario
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ids' => ['nullable', 'array'], // Para la eliminación múltiple
            'ids.*' => ['exists:entry_types,id'], // Validar que los tipos de asientos existen
        ];
    }

    /**
     * Realiza validaciones adicionales después de que las reglas básicas hayan pasado.
     */
    protected function passedValidation()
    {
        $entryTypeIds = $this->input('ids', []); // IDs para eliminación múltiple

        // Si no hay un array de IDs, usar el ID de la URL para la eliminación simple
        if (!$entryTypeIds && $this->route('entry_type')) {
            $entryTypeIds = [$this->route('entry_type')];
        }

        foreach ($entryTypeIds as $entryTypeId) {
            $entryType = EntryType::find($entryTypeId);

            // Verificar si el tipo de asiento tiene asientos asociados
            if ($entryType && $entryType->entries()->exists()) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => "El tipo de asiento '{$entryType->name}' tiene asientos asociados y no se puede eliminar."
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
