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
            'departuring' => 'sometimes|date',
            'arriving' => 'sometimes|date|after:departuring',
            'unload_starting' => 'sometimes|date|after:arriving',
            'unload_finishing' => 'sometimes|date|after:unload_starting',
            'departure_from_site' => 'sometimes|date|after:unload_finishing',
            'return_to_plant' => 'sometimes|date|after:departure_from_site',
        ];
    }
}