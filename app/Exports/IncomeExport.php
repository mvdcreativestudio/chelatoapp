<?php

namespace App\Exports;

use App\Repositories\IncomeRepository;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IncomeExport implements FromCollection, WithHeadings
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
        $grandTotalUYU = 0;
        $grandTotalUSD = 0;

        foreach ($this->incomes as $income) {
            $entityName = $income->client ? $income->client->name : ($income->supplier ? $income->supplier->name : 'Ninguno');

            // Formatear la fecha de ingreso
            $incomeDate = $income->income_date ? $income->income_date->format('d/m/Y') : 'N/A';

            // Obtener el método de pago
            $paymentMethod = $income->paymentMethod ? $income->paymentMethod->description : 'N/A';

            // Obtener la categoría del ingreso
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

            // Calcular montos en diferentes monedas
            $total = $income->income_amount;
            $rate = $income->currency_rate;
            
            if ($income->currency === 'Dólar') {
                $usdAmount = $total;
                if (!$rate || $rate == 0) {
                    $rate = app(IncomeRepository::class)->getHistoricalDollarRate($income->income_date);
                }
                $uyuAmount = $rate > 0 ? $total * $rate : 0;
            } else {
                $uyuAmount = $total;
                if (!$rate || $rate == 0) {
                    $rate = app(IncomeRepository::class)->getHistoricalDollarRate($income->income_date);
                }
                $usdAmount = $rate > 0 ? $total / $rate : 0;
            }

            $grandSubTotal += $subtotal;
            $grandTaxTotal += $taxAmount;
            $grandTotal += $total;
            $grandTotalUYU += $uyuAmount;
            $grandTotalUSD += $usdAmount;

            if (!empty($income->items)) {
                foreach ($income->items as $item) {
                    $itemTotal = $item['price'] * $item['quantity'];
                    $rows[] = [
                        'entity_name' => $entityName,
                        'income_name' => $income->income_name,
                        'income_date' => $incomeDate,
                        'payment_method' => $paymentMethod,
                        'income_category' => $incomeCategory,
                        'currency' => $income->currency ?: 'Sin Moneda',
                        'item_name' => $item['name'],
                        'item_price' => number_format($item['price'], 2),
                        'item_quantity' => $item['quantity'],
                        'item_total' => number_format($itemTotal, 2),
                        'subtotal' => number_format($subtotal, 2),
                        'tax_info' => $taxInfo,
                        'tax_amount' => number_format($taxAmount, 2),
                        'total' => number_format($total, 2),
                        'total_uyu' => number_format($uyuAmount, 2),
                        'total_usd' => number_format($usdAmount, 2)
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
            'currency' => '',
            'item_name' => '',
            'item_price' => '',
            'item_quantity' => '',
            'item_total' => 'TOTALES:',
            'subtotal' => number_format($grandSubTotal, 2),
            'tax_info' => '',
            'tax_amount' => number_format($grandTaxTotal, 2),
            'total' => number_format($grandTotal, 2),
            'total_uyu' => number_format($grandTotalUYU, 2),
            'total_usd' => number_format($grandTotalUSD, 2)
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Cliente/Proveedor',
            'Nombre del Ingreso',
            'Descripción',
            'Fecha del Ingreso',
            'Monto',
            'Método de Pago',
            'Categoría',
            'Moneda',
            'Producto',
            'Precio Unit.',
            'Cantidad',
            'Total Item',
            'Subtotal',
            'Impuesto',
            'Monto Impuesto',
            'Total Original',
            'Total en Pesos',
            'Total en Dólares'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para la fila de encabezados
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'E4E4E4']
            ]
        ]);

        // Formato de moneda para columnas de montos
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('H2:H'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J2:P'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

        // Estilo para la última fila (totales)
        $sheet->getStyle('A'.$lastRow.':P'.$lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'F0F0F0']
            ]
        ]);

        // Ajustar el ancho de las columnas automáticamente
        foreach(range('A','P') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
