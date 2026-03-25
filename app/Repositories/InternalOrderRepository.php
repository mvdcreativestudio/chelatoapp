<?php

namespace App\Repositories;

use App\Models\InternalOrder;
use App\Models\InternalOrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\InternalOrders\InternalOrderStatus;


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

        $currentStore = Store::find($storeId);

        // Si puede crear órdenes para todas las tiendas
        $fromStores = $currentStore->can_create_internal_orders_to_all_stores
            ? Store::all()
            : Store::where('id', $storeId)->get();

        // Tiendas destino siguen igual
        $toStores = $currentStore->can_create_internal_orders_to_all_stores
            ? Store::where('id', '!=', $storeId)->get()
            : Store::where('can_receive_internal_orders', true)
                    ->where('id', '!=', $storeId)
                    ->get();

        return [
            'from_stores'   => $fromStores,
            'stores'        => $toStores, // esto sigue alimentando el `to_store_id`
            'current_store' => $currentStore,
        ];
    }





    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $fromStoreId = $data['from_store_id'];

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
        $store = Store::find($storeId);

        // Si es una tienda normal, solo muestra órdenes que recibe
        if (!$store->can_create_internal_orders_to_all_stores) {
            $orders = InternalOrder::with('fromStore')
                ->where('to_store_id', $storeId)
                ->latest()
                ->get();
        } else {
            // Si es fábrica (o quien tiene el permiso), ve todas las órdenes que creó
            $orders = InternalOrder::with('toStore')
                ->where('from_store_id', $storeId)
                ->latest()
                ->get();
        }

        return [
            'orders' => $orders,
            'totals' => [
                'all' => $orders->count(),
                'pending' => $orders->where('status', InternalOrderStatus::PENDING)->count(),
                'accepted' => $orders->where('status', InternalOrderStatus::ACCEPTED)->count(),
                'delivered' => $orders->where('status', InternalOrderStatus::DELIVERED)->count(),
                'cancelled' => $orders->where('status', InternalOrderStatus::CANCELLED)->count(),
            ]
        ];
    }


    public function updateReceivedOrder(InternalOrder $order, array $data): void
    {
        // Forzar fecha de entrega si está marcada como entregada pero no se definió
        if (($data['status'] ?? null) === 'delivered' && empty($data['delivery_date'])) {
            $data['delivery_date'] = now()->toDateString(); // YYYY-MM-DD
        }

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
