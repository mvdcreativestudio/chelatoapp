<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Order;
use App\Models\Expense;
use App\Models\CurrencyRate;
use App\Models\CurrencyRateHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /*
    * Retorna los productos más vendidos
    * @param int $limit
    * @return array
    */
    public function getTopSellingProducts($limit = 10)
    {
        $orders = Order::where('payment_status', 'paid')->get();
        $productSales = [];

        foreach ($orders as $order) {
            $products = json_decode($order->products, true);
            foreach ($products as $product) {
                if (!isset($product['id'])) {
                    continue;
                }

                $productId = $product['id'];
                $quantity = $product['quantity'];
                $price = $product['price'];

                // Pasar producto de dólares a pesos.
                if (isset($product['currency']) && $product['currency'] === 'Dólar') {
                    $rate = $this->getHistoricalDollarRate($order);
                    $price *= $rate['sell'];
                }

                if (isset($productSales[$productId])) {
                    $productSales[$productId]['quantity'] += $quantity;
                    $productSales[$productId]['total_sales'] += $price * $quantity;
                } else {
                    $productModel = Product::find($productId);
                    if ($productModel) {
                        $productSales[$productId] = [
                            'id' => $productId,
                            'name' => $productModel->name,
                            'price' => $price,
                            'quantity' => $quantity,
                            'total_sales' => $price * $quantity,
                        ];
                    }
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
        $orders = Order::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('payment_status', 'paid')
            ->get();

        $incomeData = $orders->groupBy(function ($order) {
            return Carbon::parse($order->date)->day;
        })->map(function ($dayOrders) {
            return [
                'day' => Carbon::parse($dayOrders->first()->date)->day,
                'total' => $dayOrders->sum(function ($order) {
                    return $this->convertOrderAmount($order, $order->total);
                })
            ];
        })->values();

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
        $year = Carbon::now()->year;

        $currentMonthOrders = Order::whereYear('date', $year)
            ->whereMonth('date', $currentMonth)
            ->where('payment_status', 'paid')
            ->get();

        $lastMonthOrders = Order::whereYear('date', $year)
            ->whereMonth('date', $lastMonth)
            ->where('payment_status', 'paid')
            ->get();

        $currentMonthTotal = $currentMonthOrders->sum(function ($order) {
            return $this->convertOrderAmount($order, $order->total);
        });

        $lastMonthTotal = $lastMonthOrders->sum(function ($order) {
            return $this->convertOrderAmount($order, $order->total);
        });

        $percentage = $lastMonthTotal > 0
            ? round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100)
            : 0;

        $lastOrder = Order::where('payment_status', 'paid')
            ->orderBy('date', 'desc')
            ->first();

        $lastOrderInfo = [];
        if ($lastOrder) {
            $products = json_decode($lastOrder->products, true);
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
                'total' => $this->convertOrderAmount($lastOrder, $lastOrder->total)
            ];
        }

        return [
            'last_order' => $lastOrderInfo,
            'orders' => $currentMonthOrders->count(),
            'percentage' => $percentage
        ];
    }

    /*
    * Retorna la cantidad de facturas impagas que se vencen en el dia de hoy, y el total de las mismas.
    * @return array
    */
    public function getExpensesDueToday()
    {
        $today = Carbon::now()->toDateString();

        $expenses = Expense::where('status', 'unpaid')
            ->where('due_date', $today)
            ->get();

        $total = $expenses->sum('amount');

        return [
            'amount' => $expenses->count(),
            'total' => $total
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

        $orders = Order::where('payment_status', 'paid')
            ->whereDate('date', $today)
            ->get();

        $totalIncome = 0;
        foreach ($orders as $order) {
            $totalIncome += $this->convertOrderAmount($order, $order->total);
        }

        $totalExpenses = Expense::where('status', 'paid')
            ->whereDate('due_date', $today)
            ->sum('amount');

        $balance = $totalIncome - $totalExpenses;

        return [
            'income' => $totalIncome,
            'expenses' => $totalExpenses,
            'balance' => $balance
        ];
    }

    /*
    * Retorna el precio del dólar para una determinada orden.
    * @param Order $order
    * @return array
    */
    private function getHistoricalDollarRate(Order $order)
    {
        $dollar = CurrencyRate::where('name', 'Dólar')->first();

        $rate = CurrencyRateHistory::where('currency_rate_id', $dollar->id)
            ->where('created_at', '<=', $order->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$rate) {
            $rate = CurrencyRateHistory::where('currency_rate_id', $dollar->id)
                ->where('created_at', '>', $order->created_at)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        return [
            'date' => $rate->created_at->format('Y-m-d H:i:s'),
            'sell' => $rate->sell
        ];
    }

    /*
    * Convierte el monto de la orden a pesos uruguayos..
    * @param Order $order
    * @param float $amount
    * @return float
    */
    private function convertOrderAmount(Order $order, float $amount): float
    {
        if ($order->currency === 'Dólar') {
            $rate = $this->getHistoricalDollarRate($order);
            return $amount * $rate['sell'];
        }
        return $amount;
    }
}
