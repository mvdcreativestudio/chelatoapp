<?php
namespace App\Exports;

use App\Enums\Expense\ExpenseTemporalStatusEnum;
use App\Enums\Expense\ExpenseStatusEnum;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping
{
    protected $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;
    }

    /**
     * Devuelve la colección de gastos.
     */
    public function collection()
    {
        return $this->expenses;
    }

    /**
     * Mapea cada fila de datos para su exportación en Excel.
     */
    public function map($expense): array
    {
        // Obtener la traducción del estado temporal
        $translatedTemporalStatus = ExpenseTemporalStatusEnum::getTranslateds()[$expense->calculateTemporalStatus()] ?? 'N/A';

        // Obtener la traducción del estado del gasto
        $translatedExpenseStatus = ExpenseStatusEnum::getTranslateds()[$expense->status->value] ?? 'N/A';

        return [
            $expense->supplier->name ?? 'N/A', // Proveedor
            $expense->expenseCategory->name ?? 'N/A', // Categoría
            $expense->currency->name ?? 'N/A', // Moneda
            $expense->store->name ?? 'N/A', // Tienda
            $expense->concept ?? 'Sin concepto', // Concepto
            number_format($expense->amount, 2, ',', '.'), // Monto total
            number_format($expense->total_payments, 2, ',', '.'), // Total Pagado
            number_format($expense->difference_amount_paid, 2, ',', '.'), // Diferencia
            $translatedExpenseStatus, // Estado del gasto traducido
            $expense->due_date->format('d/m/Y'), // Fecha de vencimiento
            $translatedTemporalStatus, // Estado temporal traducido
        ];
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Proveedor',
            'Categoría',
            'Moneda',
            'Tienda',
            'Concepto',
            'Monto Total',
            'Total Pagado',
            'Diferencia',
            'Estado',
            'Fecha de Vencimiento',
            'Estado Temporal',
        ];
    }
}
