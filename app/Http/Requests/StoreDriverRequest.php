<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'document' => 'required|integer',
            'address' => 'required|string|max:255',
            'phone' => 'required',
            'license_date' => 'required|date',
            'is_active' => 'required|boolean',
            'health_date' => 'required|date',
        ];
    }
}
