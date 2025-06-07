<?php

namespace App\Repositories;

use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchRepository
{
    public function getAll()
    {
        return Batch::leftJoin('purchase_entries', 'batches.purchase_entries_id', '=', 'purchase_entries.id')
            ->leftJoin('purchase_order_items', 'purchase_entries.purchase_order_items_id', '=', 'purchase_order_items.id')
            ->leftJoin('raw_materials', 'purchase_order_items.raw_material_id', '=', 'raw_materials.id')
            ->leftJoin('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->leftJoin('purchase_orders', 'purchase_entries.purchase_order_items_id', '=', 'purchase_orders.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->select(
                'batches.batch_number',    // Número de lote
                'batches.quantity',        // Cantidad
                'batches.production_date', // Fecha de producción
                'batches.expiration_date', // Fecha de expiración
                'batches.created_at',      
                'purchase_entries.id as purchase_entries_id', // ID de entrada
                DB::raw('COALESCE(raw_materials.name, products.name) as item_name'),
                'suppliers.name as suppliers_name'
            )
            ->get();
    }

    public function find($id)
    {
        return Batch::findOrFail($id);
    }

    public function create(array $data)
    {
        return Batch::create($data);
    }

    public function update($id, array $data)
    {
        $batch = $this->find($id);
        $batch->update($data);
        return $batch;
    }

    public function delete($id)
    {
        $batch = Batch::find($id);
        return $batch->delete();
    }

    public function createBatches(array $data)
    {
        $batches = [];

        foreach ($data['batches'] as $batch) {
            $batches[] = [
                'batch_number' => $batch['batch_number'],
                'quantity' => $batch['quantity'],
                'production_date' => $batch['production_date'],
                'expiration_date' => $batch['expiration_date'],
                'purchase_entries_id' => $data['purchase_entries_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return Batch::insert($batches); // Inserta todos los lotes de una vez
    }
}