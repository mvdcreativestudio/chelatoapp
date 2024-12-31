<?php

namespace App\Repositories;

use App\Models\DispatchNote;
use App\Models\Order;

class DispatchNoteRepository
{
    /*
    * 
    * Obtener todos los remitos.
    */
    public function getAll()
    {
        return DispatchNote::all();
    }

    /*
    * 
    * Obtener un remito por su ID.
    */
    public function find($id)
    {
        return DispatchNote::with('order.client', 'product')->findOrFail($id);
    }

    /*
    * 
    * Crear un remito.
    */
    public function create(array $data)
    {
        return DispatchNote::create($data);
    }


    /*
    * 
    * Obtener la cantidad total despachada de un producto en una orden.
    */
    public function getTotalDispatchedQuantity($orderId, $productId)
    {
        return DispatchNote::where('order_id', $orderId)
            ->where('product_id', $productId)
            ->sum('quantity');
    }

    /*
    * 
    * Crear un remito.
    */
    public function createDispatchNote(array $data, Order $order, array $product)
    {
        $dispatchNote = DispatchNote::create([
            'order_id' => $order->id,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'date' => $data['date'],
            'bombing_type' => $data['bombing_type'],
            'delivery_method' => $data['delivery_method'],
        ]);

        return $dispatchNote;
    }

    /*
    * 
    * Actualizar un remito.
    */
    public function update($id, array $data)
    {
        $dispatchNote = $this->find($id);
        $dispatchNote->update($data);
        return $dispatchNote;
    }

    /*
    * 
    * Eliminar un remito.
    */
    public function delete($id)
    {
        $dispatchNote = $this->find($id);
        return $dispatchNote->delete();
    }

    /*
    * 
    * Obtener los remitos de una orden.
    */
    public function getByOrderId($orderId)
    {
        return DispatchNote::where('order_id', $orderId)->get();
    }

    /*
    * 
    * Obtener una orden con sus remitos.
    */
    public function getOrderWithDispatchNotes($uuid)
    {
        $order = Order::with('client')->where('uuid', $uuid)->firstOrFail();
        $dispatchNotes = DispatchNote::where('order_id', $order->id)
            ->with('noteDelivery')
            ->get();

        return compact('order', 'dispatchNotes');
    }
}
