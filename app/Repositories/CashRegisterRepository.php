<?php

namespace App\Repositories;
use App\Models\Store;

use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\ProductCategory;


class CashRegisterRepository
{
    /**
     * Obtiene todos los registros de caja registradora para la tabla de datos.
     */
    public function getCashRegistersForDatatable($userId): mixed
    {
        $query = CashRegister::select([
                'cash_registers.id',
                'cash_registers.store_id',
                'cash_registers.user_id',
                'stores.name as store_name',
                'users.name as user_name',
                'cash_register_logs.id as cash_register_log_id',
                'cash_register_logs.open_time',
                'cash_register_logs.close_time',
            ])
            ->join('stores', 'cash_registers.store_id', '=', 'stores.id')
            ->join('users', 'cash_registers.user_id', '=', 'users.id')
            ->leftJoin('cash_register_logs', function ($join) {
                $join->on('cash_register_logs.cash_register_id', '=', 'cash_registers.id')
                     ->whereRaw('cash_register_logs.id = (select max(id) from cash_register_logs where cash_register_logs.cash_register_id = cash_registers.id)');
            });

        // Filtrar los registros según el rol del usuario
        if (!Auth::user()->hasRole('Administrador')) {
            $query->where('cash_registers.user_id', $userId);
        }

        return $query->get();
    }

    public function findStoreByCashRegisterId($cashRegisterId)
    {
        $cashRegister = CashRegister::find($cashRegisterId);

        if ($cashRegister) {
            return $cashRegister->store;
        }

        return null;
    }

    /**
     * Crea un nuevo registro de caja.
     */
    public function createCashRegister(array $data): CashRegister
    {
        return CashRegister::create($data);
    }

    /**
     * Obtiene un registro de caja por su ID.
     */
    public function getCashRegisterById(int $id): ?CashRegister
    {
        return CashRegister::find($id);
    }

    /**
     * Actualiza un registro de caja existente.
     */
    public function updateCashRegister(int $id, array $data): bool
    {
        $cashRegister = CashRegister::find($id);
        if ($cashRegister) {
            return $cashRegister->update($data);
        }
        return false;
    }

    /**
     * Elimina un registro de caja por su ID.
     */
    public function deleteCashRegister(int $id): bool
    {
        $cashRegister = CashRegister::find($id);
        if ($cashRegister) {
            return $cashRegister->delete();
        }
        return false;
    }

    /**
     * Devuelve la(s) tienda(s) a las cuales le puede abrir una caja registradora.
     */
    public function storesForCashRegister()
    {
        if (!Auth::user()->hasRole('Administrador')) {
            return auth()->user()->store()->select('id', 'name')->get();
        } else {
            return Store::select('id', 'name')->get();
        }
    }

    /**
     * Devuelve los balances y ventas de la caja registradora con cálculos dinámicos.
     */
    public function getDetails($cashRegisterId){
        $details = CashRegisterLog::where('cash_register_id', $cashRegisterId)
                    ->with('expenses')
                    ->orderBy('open_time', 'DESC')
                    ->get();

        foreach ($details as $detail) {
            if ($detail->close_time) {
                $detail->cash_sales = $detail->cash_sales ?? 0;
                $detail->pos_sales = $detail->pos_sales ?? 0;
            } else {
                $sales = \DB::table('orders')
                    ->selectRaw("
                        SUM(CASE
                            WHEN payment_method = 'cash' THEN total
                            ELSE 0
                        END) as total_cash_sales,
                        SUM(CASE
                            WHEN payment_method IN ('credit', 'debit', 'card') THEN total
                            ELSE 0
                        END) as total_pos_sales,
                        SUM(CASE
                            WHEN payment_method = 'mercadopago' THEN total
                            ELSE 0
                        END) as total_mercadopago_sales,
                        SUM(CASE
                            WHEN payment_method = 'bankTransfer' THEN total
                            ELSE 0
                        END) as total_bank_transfer_sales,
                        SUM(CASE
                            WHEN payment_method = 'internalCredit' THEN total
                            ELSE 0
                        END) as total_internal_credit_sales
                    ")
                    ->where('cash_register_log_id', $detail->id)
                    ->first();

                $detail->cash_sales = $sales->total_cash_sales ?? 0;
                $detail->pos_sales = $sales->total_pos_sales ?? 0;
                $detail->mercadopago_sales = $sales->total_mercadopago_sales ?? 0;
                $detail->bank_transfer_sales = $sales->total_bank_transfer_sales ?? 0;
                $detail->internal_credit_sales = $sales->total_internal_credit_sales ?? 0;
            }

            // Calcular gastos convertidos a pesos
            $totalExpenses = 0;
            foreach ($detail->expenses as $expense) {
                if ($expense->currency === 'Dólar') {
                    $rate = $expense->currency_rate ?? $this->getDollarExchangeRate();
                    $totalExpenses += $expense->amount * $rate;
                } else {
                    $totalExpenses += $expense->amount;
                }
            }
            $detail->setAttribute('total_expenses', $totalExpenses);
        }

        return $details;
    }

    /**
     * Obtiene la cotización actual del dólar.
     */
    private function getDollarExchangeRate(): float
    {
        try {
            $dollarRate = \App\Models\CurrencyRate::where('name', 'Dólar')->first();

            if (!$dollarRate) {
                return 1;
            }

            $latestRate = \App\Models\CurrencyRateHistory::where('currency_rate_id', $dollarRate->id)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            return $latestRate ? $latestRate->sell : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Obtiene la consulta base de los detalles de una caja registradora.
     */
    public function getDetailsQuery(string $cashRegisterId)
    {
        return CashRegisterLog::where('cash_register_id', $cashRegisterId)
            ->orderBy('open_time', 'desc');
    }

    /**
     * Devuelve las ventas realizadas por una caja registradora (usando Orders).
     */
    public function getSales($id)
    {
        $sales = Order::where('cash_register_log_id', $id)
            ->with('client')
            ->get();

        foreach ($sales as $sale) {
            $sale->products = json_decode($sale->products, true);

            // Calcular cash_sales y pos_sales dinámicamente basado en payment_method
            $sale->cash_sales = $sale->payment_method === 'cash' ? $sale->total : 0;
            $sale->pos_sales = in_array($sale->payment_method, ['credit', 'debit', 'mercadopago', 'card']) ? $sale->total : 0;

            // Agregar hour para compatibilidad
            $sale->hour = $sale->time;
        }

        return $sales;
    }
}
