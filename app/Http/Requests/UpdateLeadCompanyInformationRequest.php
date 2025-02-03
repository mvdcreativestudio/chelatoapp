<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadCompanyInformationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|numeric',
            'country' => 'nullable|string|max:255',
            'webpage' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'address.max' => 'La dirección no puede exceder los 255 caracteres',
            'city.max' => 'La ciudad no puede exceder los 255 caracteres',
            'state.max' => 'El estado no puede exceder los 255 caracteres',
            'postal_code.numeric' => 'El código postal debe ser un número',
            'country.max' => 'El país no puede exceder los 255 caracteres',
            'webpage.max' => 'La página web no puede exceder los 255 caracteres',
        ];
    }
}