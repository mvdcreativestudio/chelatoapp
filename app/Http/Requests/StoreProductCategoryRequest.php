<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CompanySettings;

class StoreProductCategoryRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $categoriesHasStore = CompanySettings::where('name', 'categories_has_store')->first()->value ?? false;

        if ($categoriesHasStore) {
            if ($this->user()->can('access_global_products')) {
                $rules['store_id'] = 'required|exists:stores,id';
            } else {
                $rules['store_id'] = 'required|in:' . $this->user()->store_id;
            }
        } else {
            $rules['store_id'] = 'nullable|exists:stores,id';
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if (!$this->user()->can('access_global_products')) {
            $this->merge([
                'store_id' => $this->user()->store_id,
            ]);
        }
    }
}
