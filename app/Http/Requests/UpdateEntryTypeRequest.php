<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
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
            'name.required' => 'El nombre del tipo de asiento es obligatorio.',
            'name.string' => 'El nombre del tipo de asiento debe ser una cadena de texto.',
            'name.max' => 'El nombre del tipo de asiento no puede tener más de :max caracteres.',
            'description.string' => 'La descripción del tipo de asiento debe ser una cadena de texto.',
            'description.max' => 'La descripción del tipo de asiento no puede tener más de :max caracteres.',
        ];
    }
}
