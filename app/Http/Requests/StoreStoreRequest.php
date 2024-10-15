<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoreRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Supongamos que solo los usuarios con un rol específico pueden crear tiendas.
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'string|max:255',
            'email' => 'required|email',
            'rut' => 'required|string|max:255',
            'status' => 'required|boolean',
        ];

        return $rules;
    }
}
