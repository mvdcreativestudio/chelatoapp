<?php

namespace App\Exports;

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CurrentAccountExport implements FromCollection, WithHeadings
{
    protected $currentAccounts;

    public function __construct($currentAccounts)
    {
        $this->currentAccounts = $currentAccounts;
    }

    public function collection()
    {
        return collect($this->currentAccounts)->map(function ($currentAccount) {
            // Determinar si es cliente o proveedor
            $entityName = $currentAccount->client ? $currentAccount->client->name : ($currentAccount->supplier ? $currentAccount->supplier->name : 'N/A');

            // Calcular estado de pago en español
            $paymentStatus = '';
            $status = $currentAccount->status->value; // Obtener el valor del enum

            switch ($status) {
                case StatusPaymentEnum::PAID->value:
                    $paymentStatus = 'Pagado';
                    break;
                case StatusPaymentEnum::UNPAID->value:
                    $paymentStatus = 'No Pagado';
                    break;
                case StatusPaymentEnum::PARTIAL->value:
                    $paymentStatus = 'Pago Parcial';
                    break;
            }

            // Total debit de los créditos iniciales
            $totalDebit = $currentAccount->initialCredits->sum('total_debit');
            $totalAmount = $currentAccount->payments->sum('payment_amount');
            return [
                'entity_name' => $entityName,
                'transaction_type' => $currentAccount->transaction_type === 'Sale' ? 'Venta' : 'Compra',
                'status' => $paymentStatus,
                'total_debit' => '$' . number_format($totalDebit, 2),
                'total_amount' => '$' . number_format($totalAmount, 2),
                'currency' => $currentAccount->currency->code ?? 'N/A', // Si la moneda está presente
                'due_date' => $currentAccount->initialCredits->first()->due_date ?? 'N/A', // Primera fecha de vencimiento
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Cliente/Proveedor',
            'Tipo de Transacción',
            'Estado de Pago',
            'Total Débito',
            'Total Pagado',
            'Moneda',
            'Fecha de Vencimiento',
        ];
    }
}
