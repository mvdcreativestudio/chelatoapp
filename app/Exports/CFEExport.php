<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CFEExport implements FromCollection, WithHeadings, WithMapping
{
    protected $cfes;

    public function __construct($cfes)
    {
        $this->cfes = $cfes;
    }

    /**
     * Devuelve la colección de facturas electrónicas.
     */
    public function collection()
    {
        return new Collection($this->cfes);
    }

    /**
     * Mapea los datos para cada fila del Excel.
     */
    public function map($cfe): array
    {
        return [
            $cfe['id'] ?? 'N/A',
            $cfe['store_name'] ?? 'N/A',
            $cfe['client_name'] ?? 'N/A',
            $cfe['client_email'] ?? 'N/A',
            isset($cfe['date']) ? \Carbon\Carbon::parse($cfe['date'])->format('d/m/Y H:i:s') : 'N/A',
            $cfe['order_id'] ?? 'N/A',
            $cfe['type'] ?? 'N/A',
            $cfe['currency'] ?? 'N/A',
            number_format($cfe['total_original'] ?? 0, 2, ',', '.'),
            $cfe['serie'] ?? 'N/A',
            $cfe['cfeId'] ?? 'N/A',
            $cfe['nro'] ?? 'N/A',
            number_format($cfe['balance'] ?? 0, 2, ',', '.'),
            $cfe['caeNumber'] ?? 'N/A',
            isset($cfe['caeExpirationDate']) ? \Carbon\Carbon::parse($cfe['caeExpirationDate'])->format('d/m/Y') : 'N/A',
            $cfe['reason'] ?? 'N/A',
            isset($cfe['is_receipt']) && $cfe['is_receipt'] ? 'Sí' : 'No',
            $this->translateStatus($cfe['status'] ?? ''),
        ];
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tienda',
            'Cliente',
            'Correo Cliente',
            'Fecha',
            'ID Orden',
            'Tipo',
            'Moneda',
            'Total',
            'Serie',
            'CFE ID',
            'Número',
            'Balance',
            'Número CAE',
            'Fecha Expiración CAE',
            'Motivo',
            'Es Recibo',
            'Estado',
        ];
    }

    /**
     * Traduce los estados de CFE.
     */
    private function translateStatus(string $status): string
    {
        $statusMap = [
            'CFE_UNKNOWN_ERROR'  => 'Error desconocido',
            'CREATED'            => 'Creado',
            'PROCESSED_ACCEPTED' => 'Procesado y Aceptado',
            'PROCESSED_REJECTED' => 'Procesado y Rechazado',
            'FORMAT_REJECTED'    => 'Rechazado por Formato',
            'SCHEDULED'          => 'Programado',
        ];

        return $statusMap[$status] ?? 'Estado Desconocido';
    }
}
