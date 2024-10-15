<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
          'name' => 'sometimes|required|string|max:255',
          'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $this->route('user'),
          'password' => 'nullable|string|min:8|confirmed',
          'role' => 'sometimes|string|exists:roles,name',
          'store_id' => 'exists:stores,id',
        ];
  }
}
