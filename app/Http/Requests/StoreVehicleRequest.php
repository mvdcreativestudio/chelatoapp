<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'number' => 'required|string',
            'brand' => 'required|string',
            'plate' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'applus_date' => 'required|date',
        ];
    }
}
