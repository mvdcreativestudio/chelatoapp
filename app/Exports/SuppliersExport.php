<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SuppliersExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Supplier::get()->map(function ($supplier) {
            return [
                'ID' => $supplier->id,
                'Nombre' => $supplier->name,
                'Teléfono' => $supplier->phone,
                'Dirección' => $supplier->address,
                'Ciudad' => $supplier->city,
                'Departamento' => $supplier->state,
                'País' => $supplier->country,
                'Email' => $supplier->email,
                'Tipo Doc.' => $supplier->doc_type,
                'Número Doc.' => $supplier->doc_number,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Nombre', 'Teléfono', 'Dirección', 'Ciudad', 'Departamento', 'País',
            'Email', 'Tipo Doc.', 'Número Doc.',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00'],
                ],
            ],
        ];
    }
}
