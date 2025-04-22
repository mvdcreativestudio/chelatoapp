<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientsExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Client::with('priceLists')->get()->map(function ($client) {
            return [
                'ID' => $client->id,
                'Nombre' => $client->name,
                'Apellido' => $client->lastname,
                'Razón Social' => $client->company_name,
                'Tipo' => $client->type,
                'RUT' => "\t" . $client->rut,
                'CI' => $client->ci,
                'Email' => $client->email,
                'Teléfono' => $client->phone,
                'Dirección' => $client->address,
                'Ciudad' => $client->city,
                'Departamento' => $client->state,
                'País' => $client->country,
                'Lista de Precios' => $client->priceLists->first()->name ?? 'Sin asignar',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Nombre', 'Apellido', 'Razón Social', 'Tipo', 'RUT', 'CI',
            'Email', 'Teléfono', 'Dirección', 'Ciudad', 'Departamento', 'País', 'Lista de Precios',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Aplica estilos al encabezado
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00'], // Amarillo fluorescente
                ],
            ],
        ];
    }
}
