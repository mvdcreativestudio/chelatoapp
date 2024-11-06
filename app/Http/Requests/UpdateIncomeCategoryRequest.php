<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Puedes cambiar esta lógica según las reglas de autorización de tu aplicación
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<string>|string>
     */
    public function rules(): array
    {
        return [
            'income_name' => 'required|string|max:255|unique:income_categories,income_name,' . $this->route('income_category'),
            'income_description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'income_name.required' => 'El nombre de la categoría es obligatorio.',
            'income_name.string' => 'El nombre de la categoría debe ser una cadena de texto.',
            'income_name.max' => 'El nombre de la categoría no debe exceder los 255 caracteres.',
            'income_description.string' => 'La descripción debe ser una cadena de texto.',
            'income_description.max' => 'La descripción no debe exceder los 1000 caracteres.',
        ];
    }
}
