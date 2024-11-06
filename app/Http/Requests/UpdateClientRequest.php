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
          'name' => 'required|string|max:255',
          'lastname' => 'nullable|string|max:255',
          'type' => 'nullable|string|max:255',
          'rut' => 'nullable|string|max:255',
          'ci' => 'nullable|string|max:255',
          'address' => 'nullable|string|max:255',
          'city' => 'nullable|string|max:255',
          'state' => 'nullable|string|max:255',
          'country' => 'nullable|string|max:255',
          'phone' => 'nullable|string|max:255',
          'email' => 'required|string|email|max:255|unique:clients,email,' . $this->route('client'),
          'website' => 'nullable|url|max:255',
          'logo' => 'nullable|string|max:255',
          'price_list_id' => 'nullable|exists:price_lists,id',

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
          'name.required' => 'El nombre es obligatorio.',
          'email.required' => 'El correo electrónico es obligatorio.',
          'email.email' => 'El correo electrónico debe ser una dirección válida.',
          'email.unique' => 'El correo electrónico ya está en uso.',
      ];
    }
}
