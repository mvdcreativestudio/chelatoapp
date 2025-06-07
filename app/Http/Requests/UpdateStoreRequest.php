<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
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
        $store = $this->route('store');

        $rules = [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email'],
            'rut' => ['sometimes', 'string'],
            'tax_rate_id' => ['sometimes', 'exists:tax_rates,id'],
            'status' => 'sometimes|boolean',
        ];
        return $rules;
    }
}
