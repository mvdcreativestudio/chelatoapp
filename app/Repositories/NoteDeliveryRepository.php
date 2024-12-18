<?php

namespace App\Repositories;

use App\Models\NoteDelivery;
use App\Models\Driver;
use App\Models\Store;
use App\Models\Vehicle;

class NoteDeliveryRepository
{
    public function getAll()
    {
        return NoteDelivery::with(['vehicle', 'driver', 'store', 'dispatchNote'])->get();
    }

    /*
    * Busca una nota de entrega por su id.
    */
    public function find($id)
    {
        return NoteDelivery::with(['vehicle', 'driver', 'store', 'dispatchNote'])
            ->findOrFail($id);
    }

    /*
    * Crea una nueva nota de entrega.
    */
    public function create(array $data)
    {
        return NoteDelivery::create($data);
    }

    /*
    * Actualiza una nota de entrega.
    */
    public function update($id, array $data)
    {
        $noteDelivery = $this->find($id);
        $noteDelivery->update($data);
        return $noteDelivery;
    }

    /*
    * Elimina una nota de entrega.
    */
    public function delete($id)
    {
        $noteDelivery = $this->find($id);
        return $noteDelivery->delete();
    }

    /*
    * Obtiene las notas de entrega de un remito.
    */
    public function getByDispatchNoteId($dispatchNoteId)
    {
        return NoteDelivery::where('dispatch_note_id', $dispatchNoteId)
            ->with(['vehicle', 'driver', 'store'])
            ->get();
    }

    /*
    * Obtiene la información necesaria para el formulario de notas de entrega.
    */
    public function getInfoForForm()
    {
        return [
            'vehicles' => Vehicle::all(),
            'drivers' => Driver::all(),
            'stores' => Store::all(),
        ];
    }
}