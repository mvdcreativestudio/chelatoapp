<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadTaskRequest extends FormRequest
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
            'leads_id'      => 'integer|exists:leads,id',
            'description'   => 'nullable|string|max:255',
            'status'        => 'nullable|integer',
            'priority'      => 'nullable|integer',
            'due_date'      => 'nullable|date',
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
