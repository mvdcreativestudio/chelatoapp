<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackagingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'bulk_production_id' => 'required|integer|exists:bulk_productions,id',
            'quantity_packaged' => 'required|integer|min:1',
            'package_id' => 'required|integer',
            'tap_id' => 'nullable|integer',
            'label_id' => 'nullable|integer',
            'packaging_date' => 'required|date',
            'quantity_used' => 'required|numeric|min:0',  // Nueva validación para cantidad usada
        ];
    }
}
