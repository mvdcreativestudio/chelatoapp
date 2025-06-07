<?php

namespace App\Repositories;

use App\Models\PurchaseEntry;
use App\Models\Batch;

class PurchaseEntryRepository
{
    public function getAllByPurchaseOrderItems(array $purchaseOrderItemIds)
    {
        return PurchaseEntry::whereIn('purchase_order_items_id', $purchaseOrderItemIds)
            ->with(['batches' => function ($query) {
                $query->select('purchase_entries_id'); // Solo necesitamos el ID para verificar la existencia de lotes
            }, 'purchaseOrderItem' => function ($query) {
                $query->with(['rawMaterial', 'product']); // Cargar las relaciones de raw_materials y products
            }])
            ->get()
            ->map(function ($entry) {
                $purchaseOrderItem = $entry->purchaseOrderItem;

                // Verificamos si raw_material_id o product_id es nulo y obtenemos el nombre correspondiente
                if (!is_null($purchaseOrderItem->raw_material_id)) {
                    $entry->item_name = $purchaseOrderItem->rawMaterial->name; // Nombre del material
                } elseif (!is_null($purchaseOrderItem->product_id)) {
                    $entry->item_name = $purchaseOrderItem->product->name; // Nombre del producto
                }

                $entry->has_batches = $entry->batches->isNotEmpty();
                return $entry;
            });
    }




    public function getAll()
    {
        return PurchaseEntry::all();
    }

    public function find($id)
    {
        return PurchaseEntry::findOrFail($id);
    }

    public function create(array $data)
    {
        return PurchaseEntry::create($data);
    }

    public function update($id, array $data)
    {
        $purchaseEntry = $this->find($id);
        $purchaseEntry->update($data);
        return $purchaseEntry;
    }

    public function delete($id)
    {
        $purchaseEntry = $this->find($id);
        return $purchaseEntry->delete();
    }
}
