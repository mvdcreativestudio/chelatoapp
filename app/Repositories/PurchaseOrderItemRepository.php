<?php

namespace App\Repositories;
use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\PurchaseOrderItem;

class PurchaseOrderItemRepository
{
    public function getAll($id)
    {
        return PurchaseOrderItem::where('purchase_orders_id', $id)
            ->get();
    }

    public function find($id)
    {
        return PurchaseOrderItem::findOrFail($id);
    }

    public function create(array $data)
    {
        return PurchaseOrderItem::create($data);
    }

    public function update($id, array $data)
    {
        $purchaseOrderItem = $this->find($id);
        $purchaseOrderItem->update($data);
        return $purchaseOrderItem;
    }

    public function delete($id)
    {
        $purchaseOrderItem = PurchaseOrderItem::find($id);
        return $purchaseOrderItem->delete();
    }

    public function getRawMaterials()
    {
        return RawMaterial::all();
    }

    public function getProducts()
    {
        return Product::all();
    }
}
