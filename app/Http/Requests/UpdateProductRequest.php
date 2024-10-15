<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
      return [
          'name' => 'required|string|max:255',
          'sku' => 'nullable|string|max:255',
          'description' => 'nullable|string|max:1000',
          'type' => 'required|in:simple,configurable',
          'max_flavors' => 'nullable|integer|min:1',
          'old_price' => 'required|numeric',
          'price' => 'nullable|numeric',
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
          'image' => 'nullable',
          'recipes' => 'nullable|array',
          'recipes.*.raw_material_id' => 'nullable|exists:raw_materials,id',
          'recipes.*.quantity' => 'nullable|numeric|min:0.01',
          'recipes.*.used_flavor_id' => 'nullable|exists:flavors,id',
          'recipes.*.units_per_bucket' => 'nullable|numeric|min:1',
          'build_price' => 'nullable|numeric',
      ];
    }
}
