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
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'email' => 'required|email|unique:stores,email',
            'rut' => 'required|string|max:255|unique:stores,rut',
            'status' => 'required|boolean',
            'accepts_mercadopago' => 'required|boolean',
        ];

        if ($this->boolean('accepts_mercadopago')) {
            $rules['mercadoPagoPublicKey'] = 'required|string|max:255';
            $rules['mercadoPagoAccessToken'] = 'required|string|max:255';
        }

        return $rules;
    }

}
