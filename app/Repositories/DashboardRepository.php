<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Expense;
use App\Helpers\CompanySettingsHelper;

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
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();

        $orders = Order::where('payment_status', 'paid')->get();
        $productSales = [];

        foreach ($orders as $order) {
            $products = json_decode($order->products, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Error al decodificar JSON en el pedido {$order->id}: " . json_last_error_msg());
                continue;
            }

            foreach ($products as $product) {
                if (!isset($product['id'], $product['quantity'], $product['base_price'], $product['price'])) {
                    continue;
                }

                $productId = $product['id'];
                $quantity = $product['quantity'];

                // Usar base_price si no se incluyen impuestos
                $productPrice = $includeTaxes ? $product['price'] : $product['base_price'];

                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'id' => $productId,
                        'name' => $product['name'] ?? 'Desconocido',
                        'price' => $productPrice,
                        'quantity' => $quantity,
                        'total_sales' => $productPrice * $quantity,
                    ];
                } else {
                    $productSales[$productId]['quantity'] += $quantity;
                    $productSales[$productId]['total_sales'] += $productPrice * $quantity;
                }
            }
        }

        usort($productSales, fn($a, $b) => $b['quantity'] <=> $a['quantity']);

        return array_slice($productSales, 0, $limit);
    }



    /*
    * Retorna los datos de ingresos mensuales
    * @param int $month
    * @return Collection
    */
    public function getMonthlyIncomeData($month = null)
    {
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();
        $sumColumn = $includeTaxes ? 'total' : 'subtotal';

        if (!$month) {
            $month = Carbon::now()->month;
        }

        $year = Carbon::now()->year;

        return Order::select(
            DB::raw('DAY(date) as day'),
            DB::raw("SUM($sumColumn) as total")
        )
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('payment_status', 'paid')
            ->groupBy(DB::raw('DAY(date)'))
            ->orderBy('day')
            ->get();
    }


    /*
    * Retorna la cantidad de ordenes pagadas, muestra en porcentaje cuantas ordenes se realizaron con respecto al mes pasado y retorna también
    * la última orden realizada.
    * @return array
    */
    public function getAmountOfOrders()
    {
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();
        $sumColumn = $includeTaxes ? 'total' : 'subtotal';

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
            $topProductId = collect($products)->sortByDesc('quantity')->first()['id'] ?? null;
            $topProduct = Product::find($topProductId);

            $lastOrderInfo = [
                'product_name' => $topProduct ? $topProduct->name : 'N/A',
                'other_products' => count($products) > 1 ? count($products) - 1 : 0,
                'total' => $lastOrder->$sumColumn // Usa total o subtotal según configuración
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
