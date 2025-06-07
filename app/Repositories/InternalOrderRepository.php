<?php

namespace App\Repositories;

use App\Models\InternalOrder;
use App\Models\InternalOrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternalOrderRepository
{
    public function all()
    {
        return InternalOrder::with(['fromStore', 'toStore'])->latest()->get();
    }

    public function getFormData()
    {
        $user = Auth::user();
        $storeId = $user->store_id;

        return [
            'stores' => Store::where('can_receive_internal_orders', true)
                             ->where('id', '!=', $storeId)
                             ->get(),
        ];
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $fromStoreId = $user->store_id;

            // 1. Crear la orden
            $order = InternalOrder::create([
              'from_store_id' => $fromStoreId,
              'to_store_id' => $data['to_store_id'],
              'status' => 'pending',
              'created_by' => $user->id,
            ]);

            // 2. Crear los ítems
            foreach ($data['products'] as $productData) {
              if (empty($productData['quantity']) || $productData['quantity'] <= 0) {
                  continue; // ignorar productos no seleccionados
              }

              InternalOrderItem::create([
                  'internal_order_id' => $order->id,
                  'product_id' => $productData['product_id'],
                  'quantity' => $productData['quantity'],
              ]);
            }


            return $order;
        });
    }

    public function getReceivedOrders()
    {
        $storeId = auth()->user()->store_id;

        $orders = InternalOrder::with('fromStore')
            ->where('to_store_id', $storeId)
            ->latest()
            ->get();

        return [
            'orders' => $orders,
            'totals' => [
                'all' => $orders->count(),
                'pending' => $orders->where('status', 'pending')->count(),
                'accepted' => $orders->where('status', 'accepted')->count(),
                'delivered' => $orders->where('status', 'delivered')->count(),
            ]
        ];
    }

    public function updateReceivedOrder(InternalOrder $order, array $data): void
    {
        // 1. Actualizar estado y fecha
        $order->update([
            'status' => $data['status'] ?? $order->status,
            'delivery_date' => $data['delivery_date'] ?? $order->delivery_date,
        ]);

        // 2. Actualizar cantidades a entregar (si existen)
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemId => $itemData) {
                $item = $order->items()->find($itemId);
                if ($item && isset($itemData['deliver_quantity'])) {
                    $item->deliver_quantity = max(0, (int) $itemData['deliver_quantity']);
                    $item->save();
                }
            }
        }
    }

}
