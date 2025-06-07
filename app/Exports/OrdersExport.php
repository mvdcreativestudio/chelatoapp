<?php

namespace App\Exports;

use App\Models\Product;
use DateTime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\CashRegisterLog;
use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
            // $totalUtility = 0;
            // foreach ($products as $product) {
            //     if(!isset($product['id'])) {
            //         continue;
            //     }
            //     $productBuildPrice = Product::find($product['id'])->build_price;
            //     $buildPrice = $productBuildPrice ?? 0;
            //     if (isset($product['price'])) {
            //         $totalUtility += ($product['price'] - $buildPrice);
            //     }
            // }

            // Formatear productos como "NOMBRE x CANTIDAD"
            $productsList = collect($products)->map(function ($product) {
                return $product['name'] . ' x' . $product['quantity'] . ' x' . ' USD' . $product['price'];
            })->implode(', ');

            $userName = '—'; // Valor por defecto si no hay usuario
            Log::info('Procesando Order ID: ' . $order['id']);

            if ($order['cash_register_log_id']) {
                Log::info('Tiene cash_register_log_id: ' . $order['cash_register_log_id']);
                
                $log = CashRegisterLog::with('cashRegister.user')->find($order['cash_register_log_id']);
                
                if ($log) {
                    Log::info('CashRegisterLog encontrado: ' . $log->id);
                    
                    if ($log->cashRegister) {
                        Log::info('CashRegister encontrado: ' . $log->cashRegister->id);
                        
                        if ($log->cashRegister->user) {
                            Log::info('Usuario encontrado: ' . $log->cashRegister->user->name);
                            $userName = $log->cashRegister->user->name;
                        } else {
                            Log::info('No se encontró el usuario en el cash register');
                        }
                    } else {
                        Log::info('No se encontró cash register');
                    }
                } else {
                    Log::info('No se encontró cash_register_log con ID: ' . $order['cash_register_log_id']);
                }
            } else {
                Log::info('El order no tiene cash_register_log_id');
            }

            $paymenStatus = '';
            if ($order['payment_status'] == 'paid') {
                $paymenStatus = 'Pagado';
            } elseif ($order['payment_status'] == 'pending') {
                $paymenStatus = 'Pendiente';
            } elseif ($order['payment_status'] == 'failed') {
                $paymenStatus = 'Cancelado';
            }

            return [
                'id' => $order['id'],
                'client_name' => $order['client_name'],
                'store_name' => $order['store_name'],
                'date' => (new DateTime($order['date']))->format('d/m/Y'),
                'total' => $order['total'],
                'payment_status' => $paymenStatus,
                'is_billed' => $order['is_billed'] ? 'Facturado' : 'No Facturado',
                'user_name' => $userName,
                'products' => $productsList, // ← Acá se agrega la columna
                // 'utility' => '$' . $totalUtility // Cálculo de la utilidad total
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Tienda',
            'Fecha',
            'Total',
            'Estado de Pago',
            'Facturado',
            'Vendedor', // Este es el nuevo campo
            'Productos', // ← Nuevo encabezado
            // 'Ganancia',
        ];
    }
}
