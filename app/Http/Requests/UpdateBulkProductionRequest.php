<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBulkProductionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'formula_id' => 'required|integer|exists:formulas,id',
            'quantity_produced' => 'required|integer|min:1',
            'batch_id' => 'required|integer|exists:batches,id',
            'production_date' => 'required|date',
            'quantity_used' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'formula_id.required' => 'El campo fórmula es obligatorio.',
            'formula_id.integer' => 'El campo fórmula debe ser un número entero.',
            'formula_id.exists' => 'La fórmula seleccionada no es válida.',
            'quantity_produced.required' => 'La cantidad producida es obligatoria.',
            'quantity_produced.integer' => 'La cantidad producida debe ser un número entero.',
            'quantity_produced.min' => 'La cantidad producida debe ser al menos 1.',
            'batch_id.required' => 'El campo lote es obligatorio.',
            'batch_id.integer' => 'El campo lote debe ser un número entero.',
            'batch_id.exists' => 'El lote seleccionado no es válido.',
            'production_date.required' => 'La fecha de producción es obligatoria.',
            'production_date.date' => 'La fecha de producción no es válida.',
            'quantity_used.required' => 'La cantidad usada es obligatoria.',
            'quantity_used.integer' => 'La cantidad usada debe ser un número entero.',
            'quantity_used.min' => 'La cantidad usada debe ser al menos 1.',
        ];
    }
}
