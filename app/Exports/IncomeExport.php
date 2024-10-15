<?php

namespace App\Exports;

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
        return collect($this->incomes)->map(function ($income) {
            // Determinar si es cliente o proveedor
            $entityName = $income->client ? $income->client->name : ($income->supplier ? $income->supplier->name : 'Ninguno');

            // Formatear la fecha de ingreso
            $incomeDate = $income->income_date ? $income->income_date->format('d/m/Y') : 'N/A';

            // Obtener el método de pago
            $paymentMethod = $income->paymentMethod ? $income->paymentMethod->description : 'N/A';

            // Obtener la categoría del ingreso
            $incomeCategory = $income->incomeCategory ? $income->incomeCategory->income_name : 'N/A';

            // Retornar el arreglo para la fila del Excel
            return [
                'entity_name' => $entityName,
                'income_name' => $income->income_name,
                'income_description' => $income->income_description ?? 'N/A',
                'income_date' => $incomeDate,
                'income_amount' => '$' . number_format($income->income_amount, 2),
                'payment_method' => $paymentMethod,
                'income_category' => $incomeCategory
            ];
        });
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
            'Categoría del Ingreso'
        ];
    }
}
