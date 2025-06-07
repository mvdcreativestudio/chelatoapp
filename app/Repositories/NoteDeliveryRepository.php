<?php

namespace App\Repositories;

use App\Models\NoteDelivery;
use App\Models\Driver;
use App\Models\Store;
use App\Models\Vehicle;
use InvalidArgumentException;

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
        $this->validateDates($data);
        return NoteDelivery::create($data);
    }

    /*
    * Actualiza una nota de entrega.
    */
    public function update($id, array $data)
    {
        $noteDelivery = $this->find($id);
        $this->validateDates($data);
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

    private function validateDates(array $data)
    {
        $dateFields = [
            'departuring',
            'arriving',
            'unload_starting',
            'unload_finishing',
            'departure_from_site',
            'return_to_plant'
        ];

        $dates = [];
        foreach ($dateFields as $field) {
            if (isset($data[$field])) {
                $dates[$field] = new \DateTime($data[$field]);
            }
        }

        // Verificar el orden cronológico
        if (isset($dates['arriving']) && isset($dates['departuring'])) {
            if ($dates['arriving'] <= $dates['departuring']) {
                throw new InvalidArgumentException('La llegada debe ser posterior a la salida');
            }
        }

        if (isset($dates['unload_starting']) && isset($dates['arriving'])) {
            if ($dates['unload_starting'] <= $dates['arriving']) {
                throw new InvalidArgumentException('El inicio de descarga debe ser posterior a la llegada');
            }
        }

        if (isset($dates['unload_finishing']) && isset($dates['unload_starting'])) {
            if ($dates['unload_finishing'] <= $dates['unload_starting']) {
                throw new InvalidArgumentException('El fin de descarga debe ser posterior al inicio de descarga');
            }
        }

        if (isset($dates['departure_from_site']) && isset($dates['unload_finishing'])) {
            if ($dates['departure_from_site'] <= $dates['unload_finishing']) {
                throw new InvalidArgumentException('La salida del sitio debe ser posterior al fin de descarga');
            }
        }

        if (isset($dates['return_to_plant']) && isset($dates['departure_from_site'])) {
            if ($dates['return_to_plant'] <= $dates['departure_from_site']) {
                throw new InvalidArgumentException('El regreso a la planta debe ser posterior a la salida del sitio');
            }
        }
    }
}