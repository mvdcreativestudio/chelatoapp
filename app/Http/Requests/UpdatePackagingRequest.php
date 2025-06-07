<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackagingRequest extends FormRequest
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
            'packaging_id' => 'required|integer',
            'packaging_date' => 'required|date',
        ];
    }
}
