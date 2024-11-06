<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormulaRawMaterialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'formula_id' => 'required|exists:formulas,id',
            'raw_material_id' => 'nulable|exists:raw_materials,id',
            'quantity_required' => 'nullable|numeric|min:0',
            'step' => 'required|integer|min:1',
            'clarification' => 'nullable|string|max:255',
        ];
    }
}
