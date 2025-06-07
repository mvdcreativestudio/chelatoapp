<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageComponentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:cap,label',
            'stock' => 'required|integer|min:0',
            'cost' => 'required|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
        ];
    }
}
