<?php

namespace App\Exports;

use App\Models\NoteDelivery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NoteDeliveriesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return NoteDelivery::with(['vehicle', 'driver', 'store', 'dispatchNote'])
            ->get()
            ->map(function ($delivery) {
                return [
                    'ID' => $delivery->id,
                    'Remito N°' => $delivery->dispatch_note_id ?? 'N/A',  
                    'Vehículo' => $delivery->vehicle->number ?? '',
                    'Conductor' => $delivery->driver->name ?? '',
                    'Sucursal' => $delivery->store->name ?? '',
                    'Salida' => $delivery->departuring,
                    'Llegada' => $delivery->arriving,
                    'Inicio Descarga' => $delivery->unload_starting,
                    'Fin Descarga' => $delivery->unload_finishing,
                    'Salida Obra' => $delivery->departure_from_site,
                    'Retorno Planta' => $delivery->return_to_plant,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Remito N°',
            'Vehículo',
            'Conductor', 
            'Sucursal',
            'Salida',
            'Llegada',
            'Inicio Descarga',
            'Fin Descarga',
            'Salida Obra',
            'Retorno Planta'
        ];
    }
}