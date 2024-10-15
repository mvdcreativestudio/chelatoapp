<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlavorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'status' => 'sometimes|in:active,inactive',
            'recipes' => 'sometimes|array',
            'recipes.*.raw_material_id' => 'required_with:recipes|exists:raw_materials,id',
            'recipes.*.quantity' => 'required_with:recipes|numeric|min:1',
        ];
    }
}
