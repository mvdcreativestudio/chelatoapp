<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:simple,configurable',
            'max_flavors' => 'nullable|integer|min:1',
            'old_price' => 'required|numeric',
            'price' => 'nullable|numeric|lt:old_price',
            'discount' => 'nullable|numeric',
            'store_id' => 'required|exists:stores,id',
            'status' => 'required|boolean',
            'stock' => 'nullable|integer',
            'safety_margin' => 'nullable|numeric',
            'bar_code' => 'nullable|string|max:255',
            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',
            'flavors' => 'nullable|array',
            'flavors.*' => 'exists:flavors,id',
            'recipes' => 'nullable|array',
            'recipes.*.raw_material_id' => 'required_without:recipes.*.used_flavor_id|exists:raw_materials,id',
            'recipes.*.used_flavor_id' => 'required_without:recipes.*.raw_material_id|exists:flavors,id',
            'recipes.*.quantity' => 'required_with:recipes|numeric|min:0.01',
            'build_price' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'price.lt' => 'El precio rebajado no puede ser mayor o igual al precio normal.',
            'recipes' => 'nullable|array',
            'categories' => 'Faltó completar el campo "CATEGORÍA"',
            'recipes.*.raw_material_id' => 'required_with:recipes|exists:raw_materials,id',
            'recipes.*.quantity' => 'required_with:recipes|numeric|min:0.01',
            'build_price.numeric' => 'El precio de costo debe ser un número.',
        ];
    }

}
