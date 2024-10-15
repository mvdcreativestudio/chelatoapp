<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlavorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'status' => 'sometimes|in:active,inactive',
            'recipes' => 'required|array',
            'recipes.*.raw_material_id' => 'required|exists:raw_materials,id',
            'recipes.*.quantity' => 'required|integer|min:1'
        ];
    }
}
