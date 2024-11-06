<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use Carbon\Carbon;

class PurchaseOrderRepository
{
    public function getAll()
    {
        return $purchaseOrders = PurchaseOrder::select('purchase_orders.*', 'suppliers.name as supplier_name')
        ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
        ->get();
    }

    public function find($id)
    {
        return PurchaseOrder::findOrFail($id);
    }

    public function create(array $data)
    {
        return PurchaseOrder::create($data);
    }

    public function update($id, array $data)
    {
        $purchaseOrder = $this->find($id);
        $purchaseOrder->update($data);
        return $purchaseOrder;
    }

    public function delete($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        return $purchaseOrder->delete();
    }

    public function getPdfData($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::with(['purchaseOrderItems.rawMaterial'])
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->select('purchase_orders.*', 'suppliers.name as supplier_name')
            ->findOrFail($purchaseOrderId);

        $status = $this->getStatusText($purchaseOrder->status);

        return [
            'order' => $purchaseOrder,
            'supplier_name' => $purchaseOrder->supplier_name,
            'created_at' => $this->formatDate($purchaseOrder->created_at),
            'due_date' => $this->formatDate($purchaseOrder->due_date),
            'status' => $status,
            'items' => $purchaseOrder->purchaseOrderItems->map(function ($item) {
                return [
                    'raw_material' => $item->rawMaterial->name,
                    'quantity' => $item->quantity,
                    'currency' => $item->currency,
                    'unit_price' => $item->unit_price,
                    'total' => $item->quantity * $item->unit_price,
                ];
            }),
        ];
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 0:
                return 'Cancelada';
            case 1:
                return 'Pendiente';
            case 2:
                return 'Completada';
            default:
                return 'Desconocido';
        }
    }

    private function formatDate($date)
    {
        if ($date instanceof Carbon) {
            return $date->format('d-m-Y');
        } elseif (is_string($date)) {
            return Carbon::parse($date)->format('d-m-Y');
        }
        return 'N/A'; 
    }
}
