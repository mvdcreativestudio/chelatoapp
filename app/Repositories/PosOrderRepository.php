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

    /**
     * Actualiza el stock de los productos (incluyendo productos compuestos) en la base de datos.
     *
     * @param array $products
     * @return bool
     */
    public function updateProductStock($products)
    {
        foreach ($products as $productData) {
            Log::info('Producto: ' . $productData['id']);
            $product = null;

            // Convertir true/false a 1/0 si es necesario
            $isComposite = $productData['is_composite'] === true ? 1 : ($productData['is_composite'] === false ? 0 : $productData['is_composite']);

            // Verificar si el producto es compuesto o normal
            if ($isComposite == 1) {
                // Si es un producto compuesto, buscar en la tabla 'composite_products'
                $product = \App\Models\CompositeProduct::find($productData['id']);
            } else {
                // Si es un producto normal, buscar en la tabla 'products'
                $product = \App\Models\Product::find($productData['id']);
            }
            Log::info('Producto: ' . $product);

            // Verificar que el producto exista
            if ($product) {
                // Si el stock es null, permitir la venta pero no modificar el stock
                if ($product->stock === null) {
                    Log::info('Stock nulo');
                    continue; // Pasar al siguiente producto sin modificar el stock
                }

                // Si el stock es insuficiente, retornar false
                if ($product->stock < $productData['quantity']) {
                    Log::info('Stock insuficiente');
                    Log::info('Producto: ' . $product->name . ', Stock disponible: ' . $product->stock . ', Cantidad solicitada: ' . $productData['quantity']);
                    // Agregar un mensaje de error detallado para saber cuál producto tiene stock insuficiente
                    return [
                        'success' => false,
                        'error' => "Stock insuficiente para el producto: {$product->name}. Stock disponible: {$product->stock}, cantidad solicitada: {$productData['quantity']}"
                    ];
                }

                // Si hay suficiente stock, actualizarlo
                Log::info('Actualizando stock');
                $product->stock -= $productData['quantity'];
                $product->save();
                Log::info('Stock actualizado');
            } else {
                // Si el producto no se encuentra, retornar false
                Log::info('Producto no encontrado');
                return [
                    'success' => false,
                    'error' => "Producto no encontrado con ID: {$productData['id']}"
                ];
            }
        }
        Log::info('Stock actualizado correctamente');
        return [
            'success' => true
        ];
    }





}
