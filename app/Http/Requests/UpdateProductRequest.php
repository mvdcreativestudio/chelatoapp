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
            'currency' => 'required|in:Peso,Dólar',
            'old_price' => 'required|numeric',
            'price' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'store_id' => 'required|exists:stores,id',
            'status' => 'required|boolean',
            'show_in_catalogue' => 'required|boolean',
            'stock' => 'nullable|integer',
            'safety_margin' => 'nullable|numeric',
            'bar_code' => 'nullable|string|max:255',
            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',
            'flavors' => 'nullable|array',
            'flavors.*' => 'exists:flavors,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB = 5120KB
            'recipes' => 'nullable|array',
            'recipes.*.raw_material_id' => 'nullable|exists:raw_materials,id',
            'recipes.*.quantity' => 'nullable|numeric|min:0.01',
            'recipes.*.used_flavor_id' => 'nullable|exists:flavors,id',
            'recipes.*.units_per_bucket' => 'nullable|numeric|min:1',
            'build_price' => 'nullable|numeric',
    
            'features' => 'array',
            'features.*.name' => 'nullable|string|max:255',
    
            'sizes' => 'array',
            'sizes.*.size' => 'nullable|string|max:255',
            'sizes.*.width' => 'nullable|numeric|min:0',
            'sizes.*.height' => 'nullable|numeric|min:0',
            'sizes.*.length' => 'nullable|numeric|min:0',
    
            'colors' => 'array',
            'colors.*.color_name' => 'nullable|string|max:255',
            'colors.*.hex_code' => 'nullable|string|max:255',
        ];
    }
    

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $colors = $this->input('colors', []);
            foreach ($colors as $color) {
                if (empty($color['name']) && !empty($color['hex_code'])) {
                    $validator->errors()->add('colors', 'El campo "Nombre del color" es obligatorio si se proporciona un código HEX.');
                }
            }
        });
    }

    
}
