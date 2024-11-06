<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'is_sellable' => 'required|boolean',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'unit_of_measure' => 'required|in:L,ml',
            'size' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }
}
