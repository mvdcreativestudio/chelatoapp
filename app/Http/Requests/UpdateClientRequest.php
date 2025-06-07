<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
            'name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
                'required_if:type,individual'
            ],
            'lastname' => [
                'bail',
                'nullable',
                'string',
                'max:255',
                'required_if:type,individual'
            ],
            'company_name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
                'required_if:type,company'
            ],
            'rut' => [
                'bail',
                'nullable',
                'string',
                'max:255',
                'required_if:type,company'
            ],
            'type' => 'nullable|string|max:255',
            'ci' => 'nullable|digits:8|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
            ],
            'tax_rate_id' => [
              'nullable',
              'integer',
            ],
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|string|max:255',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'branch' => 'nullable|string|max:255',
        ];
    }


    /**
     * Obtiene los mensajes de error personalizados para la validación.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required_if' => 'El nombre es obligatorio para clientes individuales.',
            'lastname.required_if' => 'El apellido es obligatorio para clientes individuales.',
            'company_name.required_if' => 'La razón social es obligatoria para empresas.',
            'rut.required_if' => 'El RUT es obligatorio para empresas.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'El correo electrónico ya está en uso.',
        ];
    }

}
