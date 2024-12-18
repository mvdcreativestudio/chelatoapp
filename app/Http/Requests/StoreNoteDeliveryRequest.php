<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteDeliveryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'store_id' => 'required|exists:stores,id',
            'dispatch_note_id' => 'required|exists:dispatch_note,id',
            'departuring' => 'required|date',
            'arriving' => 'required|date|after:departuring',
            'unload_starting' => 'required|date|after:arriving',
            'unload_finishing' => 'required|date|after:unload_starting',
            'departure_from_site' => 'required|date|after:unload_finishing',
            'return_to_plant' => 'required|date|after:departure_from_site',
        ];
    }
}