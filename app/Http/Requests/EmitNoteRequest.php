<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmitNoteRequest extends FormRequest
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
            'noteType' => 'required|in:credit,debit',
            'noteAmount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
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
            'noteType.required' => 'El tipo de nota es obligatorio.',
            'noteType.in' => 'El tipo de nota debe ser "credit" o "debit".',
            'noteAmount.required' => 'El monto de la nota es obligatorio.',
            'noteAmount.numeric' => 'El monto de la nota debe ser un número.',
            'noteAmount.min' => 'El monto de la nota no puede ser negativo.',
            'reason.required' => 'La razón de la nota es obligatoria.',
            'reason.string' => 'La razón de la nota debe ser una cadena de texto.',
            'reason.max' => 'La razón de la nota no puede superar los 255 caracteres.',
        ];
    }
}
