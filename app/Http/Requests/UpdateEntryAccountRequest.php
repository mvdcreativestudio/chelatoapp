<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryAccountRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50'],
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
            'code.required' => 'El código de la cuenta contable es obligatorio.',
            'code.string' => 'El código de la cuenta contable debe ser una cadena de texto.',
            'code.max' => 'El código de la cuenta contable no puede tener más de :max caracteres.',
            'name.required' => 'El nombre de la cuenta contable es obligatorio.',
            'name.string' => 'El nombre de la cuenta contable debe ser una cadena de texto.',
            'name.max' => 'El nombre de la cuenta contable no puede tener más de :max caracteres.',
            'description.string' => 'La descripción de la cuenta contable debe ser una cadena de texto.',
            'description.max' => 'La descripción de la cuenta contable no puede tener más de :max caracteres.',
        ];
    }
}
