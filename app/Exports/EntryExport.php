<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EntryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $entries;

    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    /**
     * Devuelve la colección de asientos contables.
     */
    public function collection()
    {
        return $this->entries;
    }

    /**
     * Mapea los datos para cada fila del Excel.
     */
    public function map($entry): array
    {
        // Obtener los totales de Débito y Crédito
        $totalDebit = $entry->getTotalDebitAttribute();
        $totalCredit = $entry->getTotalCreditAttribute();

        // Retornar los datos de la fila
        return [
            $entry->entry_date ? $entry->entry_date->format('d/m/Y') : 'N/A',
            $entry->entryType ? $entry->entryType->description : 'N/A',
            $entry->concept ?? 'N/A',
            $entry->currency ? $entry->currency->name : 'N/A',
            $totalDebit,
            $totalCredit,
            $entry->is_balanced ? 'Sí' : 'No',
        ];
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Fecha del Asiento',
            'Tipo de Asiento',
            'Concepto',
            'Moneda',
            'Total Débito',
            'Total Crédito',
            'Balanceado',
        ];
    }
}
