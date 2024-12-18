<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteDelivery extends Model
{
    use HasFactory;

    protected $table = 'note_delivery';

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'store_id',
        'dispatch_note_id',
        'departuring',
        'arriving',
        'unload_starting',
        'unload_finishing',
        'departure_from_site',
        'return_to_plant',
    ];

    /*
    *  Relacion con el modelo Vehicle 
    */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /*
    *  Relacion con el modelo Driver 
    */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /*
    *  Relacion con el modelo Store 
    */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /*
    *  Relacion con el modelo DispatchNote 
    */
    public function dispatchNote()
    {
        return $this->belongsTo(DispatchNote::class);
    }
}