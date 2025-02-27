<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Expense;

class DashboardRepository
{
    public function getAll()
    {
        return;
    }

    public function find($id)
    {
        return;
    }

    public function create(array $data)
    {
        return;
    }

    public function update($id, array $data)
    {
        return;
    }

    public function delete($id)
    {
        return;
    }

    /**
    * Retorna los productos más vendidos
    *
    * @param int $limit
    * @return array
    */
    public function getTopSellingProducts($limit = 10)
    {
        $orders = Order::where('payment_status', 'paid')->get();
        $productSales = [];

        foreach ($orders as $order) {
            // Get products and ensure it's an array
            $products = $order->products;
            
            // If it's a string, try to decode it
            if (is_string($products)) {
                $products = json_decode($products, true);
            }

            // Skip if products is not an array
            if (!is_array($products)) {
                Log::error("Invalid products data in order {$order->id}");
                continue;
            }

            foreach ($products as $product) {
                if (!isset($product['id'], $product['quantity'])) {
                    Log::warning("Invalid product in order {$order->id}: " . json_encode($product));
                    continue;
                }

                $productId = $product['id'];
                $quantity = $product['quantity'];

                // Try to get the product model
                $productModel = Product::find($productId);
                $price = $product['price'] ?? $productModel->price ?? 0;

                if (!$price) {
                    Log::warning("Product with ID {$productId} has price 0 in order {$order->id}");
                }

                if (isset($productSales[$productId])) {
                    $productSales[$productId]['quantity'] += $quantity;
                    $productSales[$productId]['total_sales'] += $price * $quantity;
                } else {
                    $productSales[$productId] = [
                        'id' => $productId,
                        'name' => $productModel->name ?? $product['name'] ?? 'Unknown',
                        'price' => $price,
                        'quantity' => $quantity,
                        'total_sales' => $price * $quantity,
                    ];
                }
            }
        }

        usort($productSales, function ($a, $b) {
            return $b['quantity'] <=> $a['quantity'];
        });

        return array_slice($productSales, 0, $limit);
    }


    /*
    * Retorna los datos de ingresos mensuales
    * @param int $month
    * @return Collection
    */
    public function getMonthlyIncomeData($month = null)
    {
        if (!$month) {
            $month = Carbon::now()->month;
        }

        $year = Carbon::now()->year;
        $incomeData = Order::select(
            DB::raw('DAY(date) as day'),
            DB::raw('SUM(total) as total')
        )
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('payment_status', 'paid')
            ->groupBy(DB::raw('DAY(date)'))
            ->orderBy('day')
            ->get();

        return $incomeData;
    }

    /*
    * Retorna la cantidad de ordenes pagadas, muestra en porcentaje cuantas ordenes se realizaron con respecto al mes pasado y retorna también
    * la última orden realizada.
    * @return array
    */
    public function getAmountOfOrders()
    {
        $currentMonth = Carbon::now()->month;
        $lastMonth = Carbon::now()->subMonth()->month;

        $currentMonthOrders = Order::whereMonth('date', $currentMonth)
            ->where('payment_status', 'paid')
            ->count();

        $lastMonthOrders = Order::whereMonth('date', $lastMonth)
            ->where('payment_status', 'paid')
            ->count();

        $percentage = $lastMonthOrders > 0
            ? round((($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100)
            : 0;

        $lastOrder = Order::where('payment_status', 'paid')
            ->orderBy('date', 'desc')
            ->first();

        $lastOrderInfo = [];
        if ($lastOrder) {
            $products = json_decode($lastOrder->products, true);

            // Busca el producto que más se vendió en la orden.
            $maxQuantity = 0;
            $topProductId = null;
            $otherProductsCount = 0;

            foreach ($products as $product) {
                if ($product['quantity'] > $maxQuantity) {
                    $maxQuantity = $product['quantity'];
                    $topProductId = $product['id'];
                }
                $otherProductsCount++;
            }

            $topProduct = Product::find($topProductId);

            $lastOrderInfo = [
                'product_name' => $topProduct ? $topProduct->name : 'N/A',
                'other_products' => $otherProductsCount > 1 ? $otherProductsCount - 1 : 0,
                'total' => $lastOrder->total
            ];
        }
        return [
            'last_order' => $lastOrderInfo,
            'orders' => $currentMonthOrders,
            'percentage' => $percentage
        ];
    }

    /*
    * Retorna la cantidad de facturas impagas y el total de las mismas.
    * @return array
    */
    public function getUnpaidExpensesSummary()
    {
        $unpaidExpenses = Expense::where('status', 'unpaid')->get();

        $total = $unpaidExpenses->sum('amount');
        $count = $unpaidExpenses->count();

        return [
            'count' => $count,
            'total' => $total,
        ];
    }

    /*
    * Retorna el total de gastos del mes actual y retorna el porcentaje de diferencia
    * con respecto al mes pasado.
    * @return float
    */
    public function getMonthlyExpensesPaid()
    {
        $currentMonth = Carbon::now()->month;
        $lastMonth = Carbon::now()->subMonth()->month;

        $currentMonthExpenses = Expense::whereMonth('due_date', $currentMonth)
            ->where('status', 'paid')
            ->sum('amount');

        $lastMonthExpenses = Expense::whereMonth('due_date', $lastMonth)
            ->where('status', 'paid')
            ->sum('amount');

        $percentage = $lastMonthExpenses > 0
            ? round((($currentMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100)
            : 0;

        return [
            'total' => $currentMonthExpenses,
            'percentage' => $percentage
        ];
    }

    /*
    * Retorna el balance diario, especificando ingresos, egresos y el total.
    * @return array
    */
    public function getDailyBalance()
    {
        $today = Carbon::now()->toDateString();

        $totalIncome = Order::where('payment_status', 'paid')
        ->whereDate('date', $today)
        ->sum(DB::raw('total - COALESCE(tax, 0)'));

        $totalTax = Order::where('payment_status', 'paid')
        ->whereDate('date', $today)
        ->sum('tax');


        $totalExpenses = Expense::where('status', 'paid')
        ->whereDate('due_date', $today)
        ->sum('amount');

        $balance = $totalIncome + $totalTax - $totalExpenses;

        return [
            'income' => $totalIncome,
            'expenses' => $totalExpenses,
            'balance' => $balance,
            'tax' => $totalTax
        ];
    }

    /**
     * Retorna el total de IVA cobrado en el día
     *
     *
     */
    public function getDailyTax()
    {
        $tax = Order::whereDate('date', Carbon::now())
            ->where('payment_status', 'paid')
            ->sum('tax');

        return $tax;
    }


}
