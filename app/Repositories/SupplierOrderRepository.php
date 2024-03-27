<?php

namespace App\Repositories;

use App\Models\SupplierOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\RawMaterial;

class SupplierOrderRepository
{
    /**
     * Devuelve todas las ordenes de compra.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll(): Collection
    {
        if (auth()->user() && auth()->user()->can('view_all_supplier-orders')) {
            return SupplierOrder::with('supplier')->get();
        } else {
            $storeId = auth()->user()->store_id;
            return SupplierOrder::where('store_id', $storeId)->get();
        }
    }

    /**
     * Busca una orden de compra por el ID.
     *
     * @param  int $id
     * @return SupplierOrder|null
     */
    public function findById($id): ?SupplierOrder
    {
        return SupplierOrder::find($id);
    }

    /**
     * Guarda una nueva orden de compra.
     *
     * @param  array $data
     * @return SupplierOrder
    */
    public function create(array $data): SupplierOrder
    {
        $data['store_id'] = auth()->user()->store_id ?? throw new ModelNotFoundException('No se puede crear una orden de compra sin una tienda asignada.');

        $order = SupplierOrder::create($data);

        if (isset($data['raw_materials'])) {
            foreach ($data['raw_materials'] as $rawMaterialId => $quantity) {
                if ($quantity > 0) {
                    $order->rawMaterials()->attach($rawMaterialId, ['quantity' => $quantity]);
                }
            }
        }

        return $order;
    }

    /**
     * Actualiza una orden de compra existente y ajusta el stock de las materias primas si es necesario.
     *
     * @param  SupplierOrder $supplierOrder
     * @param  array $data
     * @return SupplierOrder
     */
    public function update(SupplierOrder $supplierOrder, array $data): SupplierOrder
    {
        $supplierOrder->update($data);

        if (isset($data['raw_materials'])) {
            foreach ($data['raw_materials'] as $rawMaterialId => $newQuantity) {
                if ($newQuantity > 0) {
                    if ($supplierOrder->rawMaterials()->find($rawMaterialId)) {
                        $currentQuantity = $supplierOrder->rawMaterials()->find($rawMaterialId)->pivot->quantity;
                        $difference = $newQuantity - $currentQuantity;

                        if ($supplierOrder->shipping_status == 'completed') {
                            $rawMaterial = RawMaterial::find($rawMaterialId);
                            $rawMaterial->increment('stock', $difference);
                        }

                        $supplierOrder->rawMaterials()->updateExistingPivot($rawMaterialId, ['quantity' => $newQuantity]);
                    } else {
                        $supplierOrder->rawMaterials()->attach($rawMaterialId, ['quantity' => $newQuantity]);

                        if ($supplierOrder->shipping_status == 'completed') {
                            RawMaterial::find($rawMaterialId)->increment('stock', $newQuantity);
                        }
                    }
                } else {
                    if ($supplierOrder->shipping_status == 'completed' && $supplierOrder->rawMaterials()->find($rawMaterialId)) {
                        $currentQuantity = $supplierOrder->rawMaterials()->find($rawMaterialId)->pivot->quantity;
                        RawMaterial::find($rawMaterialId)->decrement('stock', $currentQuantity);
                    }
                    $supplierOrder->rawMaterials()->detach($rawMaterialId);
                }
            }
        }

        return $supplierOrder;
    }

    /**
     * Elimina una orden de compra y ajusta el stock de las materias primas si la orden está completada.
     *
     * @param  SupplierOrder $supplierOrder
     * @return bool|null
     */
    public function delete(SupplierOrder $supplierOrder): ?bool
    {
        if ($supplierOrder->shipping_status == 'completed') {
            foreach ($supplierOrder->rawMaterials as $rawMaterial) {
                $quantity = $rawMaterial->pivot->quantity;
                $rawMaterial->decrement('stock', $quantity);
            }
        }

        return $supplierOrder->delete();
    }


    /**
     * Actualiza el stock de las materias primas cuando una orden se completa.
     *
     * @param int $orderId
    */
    public function completeOrder(int $orderId)
    {
        $order = SupplierOrder::find($orderId);

        if (!$order) {
            throw new ModelNotFoundException("Orden no encontrada");
        }

        $order->shipping_status = 'completed';
        $order->save();

        foreach ($order->rawMaterials as $rawMaterial) {
            $quantity = $rawMaterial->pivot->quantity;
            $rawMaterial->increment('stock', $quantity);
        }
    }
}
