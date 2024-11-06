<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EntryDetailsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    /**
     * Devuelve la colección de detalles del asiento.
     */
    public function collection()
    {
        return $this->entry->details;
    }

    /**
     * Mapea cada detalle del asiento para su exportación en una fila de Excel.
     */
    public function map($detail): array
    {
        return [
            $detail->entryAccount->code . ' - ' . $detail->entryAccount->name,
            $detail->iva ? $detail->iva . '%' : 'Sin IVA',
            $detail->concept ?? '-',
            number_format($detail->amount_debit, 2, ',', '.'),
            number_format($detail->amount_credit, 2, ',', '.'),
        ];
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Cuenta contable',
            'IVA',
            'Concepto',
            'Debe',
            'Haber',
        ];
    }
}
