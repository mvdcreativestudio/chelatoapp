<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; 
    }

    /**
     * Reglas de validación para almacenar una columna Kanban.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_creator_id' => 'nullable|exists:users,id',
            'store_id'        => 'nullable|exists:stores,id',
            'client_id'       => 'nullable|integer',
            'type'            => 'nullable|in:company,individual,no-client',
            'archived'        => 'nullable|boolean',
            'name'            => 'sometimes|required|string|max:255',
            'description'     => 'nullable|string|max:500',
            'amount_of_money' => 'nullable|numeric|min:0',
            'category_id'     => 'nullable|integer',
            'phone'           => 'nullable|string|max:15',
            'email'           => 'nullable|email|max:255',
            'position'        => 'nullable|integer',
        ];
    }

    /**
     * Mensajes de error personalizados.
     *
     * @return array
     */
    public function messages()
    {
        return [
           
        ];
    }
}
