<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteDeliveryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'driver_id' => 'sometimes|exists:drivers,id',
            'store_id' => 'sometimes|exists:stores,id',
            'dispatch_note_id' => 'sometimes|exists:dispatch_note,id',
            'departuring' => 'nullable|date',
            'arriving' => 'nullable|date',
            'unload_starting' => 'nullable|date',
            'unload_finishing' => 'nullable|date',
            'departure_from_site' => 'nullable|date',
            'return_to_plant' => 'nullable|date',
        ];
    }
}