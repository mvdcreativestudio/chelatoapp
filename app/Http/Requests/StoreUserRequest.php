<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8|confirmed',
      'role' => 'required|string|exists:roles,name',
      'store_id' => 'exists:stores,id',
    ];
  }
}
