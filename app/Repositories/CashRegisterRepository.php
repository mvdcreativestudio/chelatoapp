<?php

namespace App\Repositories;
use App\Models\Store;

use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\PosOrder;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\ProductCategory;



class CashRegisterRepository
{
    /**
     * Obtiene todos los registros de caja registradora para la tabla de datos.
     *
     * @return mixed
    */
    public function getCashRegistersForDatatable($userId): mixed
    {
        $query = CashRegister::select([
                'cash_registers.id',
                'cash_registers.store_id',
                'cash_registers.user_id',
                'stores.name as store_name',
                'users.name as user_name',
                'cash_register_logs.open_time',  // Último open_time
                'cash_register_logs.close_time'  // Último close_time
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
            return $cashRegister->store_id;
        }

        return null;
    }

    /**
     * Crea un nuevo registro de caja.
     *
     * @param array $data
     * @return CashRegister
    */
    public function createCashRegister(array $data): CashRegister
    {
        return CashRegister::create($data);
    }

    /**
     * Obtiene un registro de caja por su ID.
     *
     * @param int $id
     * @return CashRegister|null
    */
    public function getCashRegisterById(int $id): ?CashRegister
    {
        return CashRegister::find($id);
    }

    /**
     * Actualiza un registro de caja existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
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
     *
     * @param int $id
     * @return bool
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
     * Devuelve la(s) tienda(s) a las cuales le puede abrir una caha registradora.
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
     * Devuelve los balances y ventas de la caja registradora.
     *
     * @param $cashRegisterId
     */
    public function getDetails($cashRegisterId){
        return CashRegisterLog::where('cash_register_id', $cashRegisterId)
                    ->orderBy('open_time', 'DESC')
                      ->get();;
    }

    /**
     * Devuelve las ventas realizadas por una caja registradora.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getSales($id)
    {
        $sales = PosOrder::where('cash_register_log_id', $id)->get();

        // Iterar sobre cada venta para decodificar los productos
        foreach ($sales as $sale) {
            $sale->products = json_decode($sale->products, true); // Decodificar el JSON a un array
        }

        return $sales;
    }

}
