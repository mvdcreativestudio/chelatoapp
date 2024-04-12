<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Client;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function getAllOrders()
    {
        return Order::all();
    }

    public function createOrder(array $clientData, array $orderData, array $cart)
    {
        DB::beginTransaction();
        try {
            $client = Client::firstOrCreate(['email' => $clientData['email']], $clientData);
            $order = new Order($orderData);
            $order->client()->associate($client);
            $order->save();

            foreach ($cart as $item) {
                $order->products()->attach($item['id'], [
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? $item['old_price'],
                ]);
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getOrdersForDataTable()
    {
      $query = Order::select([
                  'orders.id',
                  'orders.date',
                  'orders.time',
                  'orders.client_id',
                  'orders.store_id',
                  'orders.subtotal',
                  'orders.tax',
                  'orders.shipping',
                  'orders.coupon_id',
                  'orders.coupon_amount',
                  'orders.discount',
                  'orders.total',
                  'orders.payment_status',
                  'orders.shipping_status',
                  'orders.payment_method',
                  'orders.shipping_method',
                  'orders.shipping_tracking',
                  'clients.email as client_email',
                  'stores.name as store_name',
                  DB::raw("CONCAT(clients.name, ' ', clients.lastname) as client_name")
                ])
              ->join('clients', 'orders.client_id', '=', 'clients.id')
              ->join('stores', 'orders.store_id', '=', 'stores.id');


      return DataTables::of($query)->make(true);
    }

    public function getOrderProductsForDataTable(Order $order)
    {
        $query = OrderProduct::where('order_id', $order->id)
            ->with([
                'product.categories:id,name',
                'product.store:id,name',
                'product.flavors:id,name'
            ])
            ->select(['id', 'product_id', 'quantity', 'price']);

        return DataTables::of($query)
            ->addColumn('product_name', function ($orderProduct) {
                $productName = $orderProduct->product->name;
                $flavors = $orderProduct->product->flavors->pluck('name')->implode(', ');
                return $flavors ? $productName . "<br><small>$flavors</small>" : $productName;
            })
            ->addColumn('category', function ($orderProduct) {
                return $orderProduct->product->categories->implode('name', ', ');
            })
            ->addColumn('store_name', function ($orderProduct) {
                return $orderProduct->product->store->name;
            })
            ->addColumn('total_product', function ($orderProduct) {
                return number_format($orderProduct->quantity * $orderProduct->price, 2);
            })
            ->rawColumns(['product_name'])  // Indica a DataTables que no escape HTML en la columna 'product_name'
            ->make(true);
    }


    public function loadOrderRelations(Order $order)
    {
        return $order->load('client', 'products');
    }

    public function destroyOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->delete();
    }
}
