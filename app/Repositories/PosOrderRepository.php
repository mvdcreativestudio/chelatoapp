<?php

namespace App\Repositories;

use App\Models\PosOrder;
use Illuminate\Support\Facades\Log;

class PosOrderRepository
{
    protected $model;

    public function __construct(PosOrder $posOrder)
    {
        $this->model = $posOrder;
    }

    /**
     * Obtiene todos los registros de caja registradora para la tabla de datos.
     *
     * @return mixed
    */
    public function getPosOrdersForDatatable($userId): mixed
    {

        $query = PosOrder::select(['pos_orders.id', 'pos_orders.date', 'pos_orders.hour', 'pos_orders.cash_register_log_id', 'pos_orders.cash_sales', 'pos_orders.pos_sales', 'pos_orders.discount', 'client_id'])
                     ->join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
                     ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
                     ->where('cash_registers.user_id', $userId)
                     ->get();

         return $query;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Busca una orden addo un id.
     *
     * @param string $id
     *
     */

    public function findById($id)
    {
        return $this->model->find($id);
    }


    /**
     * Guarda un registro en la base de datos.
     *
     * @param array $data
     *
     */

    public function create(array $data)
    {
        return PosOrder::create($data);
    }


    /**
     * Actualiza un registro en la base de datos dado un ID.
     *
     * @param  $id
     * @param array $data
     * @return bool
     *
     */
    public function update($id, array $data): bool
    {
        $posOrder = PosOrder::find($id);
        if ($posOrder) {
            return $posOrder->update($data);
        }
        return false;
    }

    /**
     * Borra un registro de la base de datos dado un ID.
     *
     * @param  $id
     * @param array $data
     *
     */

    public function delete($id)
    {
        $posOrder = $this->findById($id);
        if ($posOrder) {
            return $posOrder->delete();
        }
        return null;
    }

    /**
     * Calcula el precio después de aplicar un descuento porcentual.
     *
     * @param int $price
     * @param int $percentage
     * @return int
     */
    public function percentageDiscount($price, $percentage)
    {
        if ($percentage >= 100) {
            return 0;
        }
        $discount = ($price * $percentage) / 100;
        return $price - $discount;
    }

    /**
     * Calcula el precio después de aplicar un descuento fijo.
     *
     * @param int $price
     * @param int $discount
     * @return int
     */
    public function fixedDiscount($price, $discount)
    {
        if ($discount >= $price) {
            return 0;
        }
        return $price - $discount;
    }

    /**
     * Calcula el vuelto a devolver.
     *
     * @param int $total
     * @param int $money
     * @return int
     */
    public function calculateDifference($total, $money)
    {
        if ($money < $total) {
            return 0;
        }
        return $money - $total;
    }
}
