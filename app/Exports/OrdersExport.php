<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return collect($this->orders)->map(function ($order) {
            $products = json_decode($order['products'], true);
            $totalUtility = 0;

            foreach ($products as $product) {
                $productBuildPrice = Product::find($product['id'])->build_price;
                $buildPrice = $productBuildPrice ?? 0;
                if (isset($product['price'])) {
                    $totalUtility += ($product['price'] - $buildPrice);
                }
            }

            $paymenStatus = '';
            if ($order['payment_status'] == 'paid') {
                $paymenStatus = 'Pagado';
            } elseif ($order['payment_status'] == 'pending') {
                $paymenStatus = 'Pendiente';
            } elseif ($order['payment_status'] == 'failed') {
                $paymenStatus = 'Cancelado';
            } elseif ($order['payment_status'] == 'refunded') {
                $paymenStatus = 'Reembolsado';
            } elseif ($order['payment_status'] == 'partial_refunded') {
                $paymenStatus = 'Reembolso parcial';
            }

            return [
                'id' => $order['id'],
                'client_name' => $order['client_name'],
                'store_name' => $order['store_name'],
                'date' => $order['date'],
                'total' => $order['total'],
                'payment_status' => $paymenStatus,
                'is_billed' => $order['is_billed'] ? 'Facturado' : 'No Facturado',
                'utility' => '$' . $totalUtility // Cálculo de la utilidad total
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Empresa',
            'Fecha',
            'Total',
            'Estado de Pago',
            'Facturado',
            'Utilidad',
        ];
    }
}
