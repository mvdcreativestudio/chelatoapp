<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
     * Modifica los datos antes de la validación.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => $this->capitalize($this->input('name')),
            'lastname' => $this->capitalize($this->input('lastname')),
            'company_name' => $this->capitalize($this->input('company_name')),
            'address' => $this->capitalize($this->input('address')),
            'city' => $this->capitalize($this->input('city')),
            'state' => $this->capitalize($this->input('state')),
            'country' => $this->capitalize($this->input('country')),
            'email' => strtolower($this->input('email')), // Convertir el email a minúsculas
        ]);
    }

    /**
     * Función para capitalizar la primera letra de cada palabra y poner el resto en minúsculas.
     *
     * @param string|null $value
     * @return string|null
     */
    protected function capitalize($value)
    {
        return $value ? ucwords(strtolower($value)) : null;
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
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'individual' && is_null($value)) {
                        $fail('El nombre es obligatorio para clientes individuales.');
                    }
                },
            ],
            'lastname' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'individual' && is_null($value)) {
                        $fail('El apellido es obligatorio para clientes individuales.');
                    }
                },
            ],
            'ci' => [
              'nullable',
              'string',
              'max:255',
              function ($attribute, $value, $fail) {
                  if ($this->type === 'individual' && is_null($value)) {
                      $fail('La CI es obligatoria para clientes individuales.');
                  }
              },
            ],
            'type' => 'required|string|max:255',
            'rut' => [
              'nullable',
              'string',
              'max:255',
              function ($attribute, $value, $fail) {
                if ($this->type === 'company' && is_null($value)) {
                  $fail('El RUT de la empresa es obligatorio en este tipo de cliente');
                }
              },
            ],
            'company_name' => [
              'nullable',
              'string',
              'max:255',
              function ($attribute, $value, $fail) {
                if ($this->type === 'company' && is_null($value)) {
                  $fail('La Razón Social de la empresa es obligatorio en este tipo de cliente');
                }
              },
            ],
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255',
            'website' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'store_id' => 'nullable|integer',
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
            'rut.required_if' => 'El RUT es obligatorio para clientes de tipo Empresa',
            'company_name.required_if' => 'La Razón Social es obligatoria para clientes de tipo Empresa',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'El correo electrónico ya está en uso.',
        ];
    }
}
