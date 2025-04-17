<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeExport implements FromCollection, WithHeadings, WithStyles
{
    protected $incomes;

    public function __construct($incomes)
    {
        $this->incomes = $incomes;
    }

    public function collection()
    {
        $rows = [];
        $grandSubTotal = 0;
        $grandTaxTotal = 0;
        $grandTotal = 0;

        foreach ($this->incomes as $income) {
            $entityName = $income->client ? $income->client->name : ($income->supplier ? $income->supplier->name : 'Ninguno');
            $incomeDate = $income->income_date ? $income->income_date->format('d/m/Y') : 'N/A';
            $paymentMethod = $income->paymentMethod ? $income->paymentMethod->description : 'N/A';
            $incomeCategory = $income->incomeCategory ? $income->incomeCategory->income_name : 'N/A';

            // Calcular subtotal de items
            $subtotal = collect($income->items)->reduce(function ($carry, $item) {
                return $carry + ($item['price'] * $item['quantity']);
            }, 0);

            // Calcular impuestos considerando el tax_rate del cliente
            $taxAmount = 0;
            $taxInfo = '-';
            
            if ($income->client && $income->client->tax_rate_id) {
                $clientTax = \App\Models\TaxRate::find($income->client->tax_rate_id);
                if ($clientTax && $clientTax->rate == 0) {
                    $taxInfo = "Exento ({$clientTax->name})";
                } elseif ($income->tax_rate_id && $income->taxRate) {
                    $taxAmount = $subtotal * ($income->taxRate->rate / 100);
                    $taxInfo = "{$income->taxRate->name} ({$income->taxRate->rate}%)";
                }
            } elseif ($income->tax_rate_id && $income->taxRate) {
                $taxAmount = $subtotal * ($income->taxRate->rate / 100);
                $taxInfo = "{$income->taxRate->name} ({$income->taxRate->rate}%)";
            }

            $total = $income->income_amount;
            $grandSubTotal += $subtotal;
            $grandTaxTotal += $taxAmount;
            $grandTotal += $total;

            if (!empty($income->items)) {
                foreach ($income->items as $item) {
                    $itemTotal = $item['price'] * $item['quantity'];
                    $rows[] = [
                        'entity_name' => $entityName,
                        'income_name' => $income->income_name,
                        'income_date' => $incomeDate,
                        'payment_method' => $paymentMethod,
                        'income_category' => $incomeCategory,
                        'item_name' => $item['name'],
                        'item_price' => number_format($item['price'], 2),
                        'item_quantity' => $item['quantity'],
                        'item_total' => number_format($itemTotal, 2),
                        'subtotal' => number_format($subtotal, 2),
                        'tax_info' => $taxInfo,
                        'tax_amount' => number_format($taxAmount, 2),
                        'total' => number_format($total, 2)
                    ];
                }
            }
        }

        // Agregar fila de totales
        $rows[] = [
            'entity_name' => '',
            'income_name' => '',
            'income_date' => '',
            'payment_method' => '',
            'income_category' => '',
            'item_name' => '',
            'item_price' => '',
            'item_quantity' => '',
            'item_total' => 'TOTALES:',
            'subtotal' => number_format($grandSubTotal, 2),
            'tax_info' => '',
            'tax_amount' => number_format($grandTaxTotal, 2),
            'total' => number_format($grandTotal, 2)
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Cliente/Proveedor',
            'Nombre del Ingreso',
            'Fecha',
            'Método de Pago',
            'Categoría',
            'Producto',
            'Precio Unit.',
            'Cantidad',
            'Total Item',
            'Subtotal',
            'Impuesto',
            'Monto Impuesto',
            'Total'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para la fila de encabezados
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'E4E4E4']
            ]
        ]);

        // Formato de moneda para columnas de montos
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('G2:G'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('I2:I'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J2:J'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('L2:M'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

        // Estilo para la última fila (totales)
        $sheet->getStyle('A'.$lastRow.':M'.$lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'F0F0F0']
            ]
        ]);

        // Ajustar el ancho de las columnas automáticamente
        foreach(range('A','M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}