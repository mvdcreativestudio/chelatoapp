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
            'ecommerce' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
            'accepts_mercadopago' => 'required|boolean',
            'invoices_enabled' => 'boolean',
            'accepts_peya_envios' => 'sometimes|boolean',
        ];

        if ($this->boolean('invoices_enabled')) {
            $rules += [
                'pymo_user' => 'required|string|max:255',
                'pymo_password' => 'required|string|max:255',
                'automatic_billing' => 'boolean',
            ];
        }


        if ($this->boolean('accepts_peya_envios')) {
            $rules += [
                'peya_envios_key' => 'required|string|max:255',
            ];
        }

        if ($this->boolean('accepts_mercadopago')) {
            $rules += [
                'mercadoPagoPublicKey' => 'required|string|max:255',
                'mercadoPagoAccessToken' => 'required|string|max:255',
            ];
        }

        if ($this->boolean('scanntech')) {
            $rules += [
                'scanntechCompany' => 'required|string|max:255',
                'scanntechBranch' => 'required|string|max:255',
            ];
        }

        return $rules;
    }
}
