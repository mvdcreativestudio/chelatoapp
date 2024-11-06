<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormulaRawMaterialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'formula_id' => 'required|exists:formulas,id',
            'raw_material_id' => 'nullable|exists:raw_materials,id',
            'quantity_required' => 'nullable|numeric|min:0',
            'step' => 'required|string|max:255',
            'clarification' => 'nullable|string|max:255',
        ];
    }
}
