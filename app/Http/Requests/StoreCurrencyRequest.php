<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
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
            'code' => 'required|string|max:10|unique:currencies,code',
            'symbol' => 'required|string|max:5',
            'name' => 'required|string|max:100',
            'exchange_rate' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'El código de la moneda es obligatorio.',
            'code.string' => 'El código de la moneda debe ser una cadena de texto.',
            'code.max' => 'El código de la moneda no puede exceder los 10 caracteres.',
            'code.unique' => 'El código de la moneda ya existe.',
            'symbol.required' => 'El símbolo de la moneda es obligatorio.',
            'symbol.string' => 'El símbolo de la moneda debe ser una cadena de texto.',
            'symbol.max' => 'El símbolo de la moneda no puede exceder los 5 caracteres.',
            'name.required' => 'El nombre de la moneda es obligatorio.',
            'name.string' => 'El nombre de la moneda debe ser una cadena de texto.',
            'name.max' => 'El nombre de la moneda no puede exceder los 100 caracteres.',
            'exchange_rate.required' => 'El tipo de cambio es obligatorio.',
            'exchange_rate.numeric' => 'El tipo de cambio debe ser un número.',
            'exchange_rate.min' => 'El tipo de cambio no puede ser negativo.',
        ];
    }
}
