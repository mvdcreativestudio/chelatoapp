<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Supongamos que solo los usuarios con un rol específico pueden modificar horarios.
        return true;
    }

    public function rules(): array
    {
      return [
          'hours' => 'array|required',
          'hours.*.open' => 'nullable|date_format:H:i',  // 'required_with:hours.*.close' podría no ser necesario si es abierto todo el día
          'hours.*.close' => 'nullable|date_format:H:i|after:hours.*.open',
      ];
    }

}
