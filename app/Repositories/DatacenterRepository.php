<?php
namespace App\Repositories;

use App\Helpers\Helpers;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\CurrencyRate;
use App\Models\CurrencyRateHistory;
use App\Models\Order;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DatacenterRepository
{
    /**
     * Obtiene el rango de fechas basado en el período seleccionado.
     *
     * @param string $period
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getDateRange(string $period, string $startDate = null, string $endDate = null): array
    {
        $today = Carbon::today();
        $start = $startDate ? Carbon::parse($startDate) : $today;
        $end   = $endDate ? Carbon::parse($endDate) : $today;

        switch ($period) {
            case 'today':
                $start = $today->copy()->startOfDay();
                $end   = $today->copy()->endOfDay();
                return [$start, $end];
            case 'week':
                return [$today->copy()->subDays(6)->startOfDay(), $today->endOfDay()];
            case 'month':
                $start = $today->copy()->startOfMonth();
                $end   = $today->copy()->endOfMonth();
                return [$start, $end];
            case 'year':
                $start = $today->copy()->startOfYear();
                $end   = $today->copy()->endOfYear();
                return [$start, $end];
            case 'always':
                $firstSale = Order::min('date') ?? PosOrder::min('date');
                $start     = $firstSale ? Carbon::parse($firstSale)->startOfMonth() : Carbon::minValue();
                $end       = $end ?? Carbon::maxValue();
                return [$start, $end];
            case 'custom':
                return [$start->startOfDay(), $end->endOfDay()];
            default:
                return [$today->copy()->startOfYear(), $today->copy()->endOfYear()];
        }
    }

    /**
     * Contar la cantidad de locales.
     *
     * @return int
     */
    public function countStores(): int
    {
        return Store::count();
    }

    /**
     * Contar la cantidad de clientes con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return int
     */
    public function countClients(string $startDate, string $endDate, int $storeId = null): int
    {
        $query = Client::query();

        // Obtener configuración de companySettings usando el provider
        $companySettings = App::make('companySettings');

        // Verificar si clients_has_store está habilitado
        if ($companySettings && $companySettings->clients_has_store == 1) {
            if (Gate::allows('view_all_datacenter')) {
                // Si el usuario tiene el permiso, puede ver datos de todas las tiendas
                if ($storeId) {
                    $query->where('store_id', $storeId);
                }
            } else {
                // Si no tiene el permiso, solo puede ver datos de su tienda
                $query->where('store_id', Auth::user()->store_id);
            }
        } else {
            // Si clients_has_store no está habilitado, se filtra por store_id si está definido
            if ($storeId) {
                $query->where('store_id', $storeId);
            } else {
                $query->whereNull('store_id');
            }
        }

        return $query->count();
    }

    /**
     * Contar la cantidad de productos con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return int
     */
    public function countProducts(string $startDate, string $endDate, int $storeId = null): int
    {
        $query = Product::query(); // Inicializa la consulta para contar productos

        // Filtra por store_id si es proporcionado
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->count(); // Retorna el total de productos contados
    }

    /**
     * Contar la cantidad de categorías
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return int
     */
    public function countCategories(int $storeId = null): int
    {
        $query = ProductCategory::query();

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->count();
    }

    /**
     * Contar la cantidad de órdenes con diferentes estados de envío con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function countOrders(string $startDate, string $endDate, int $storeId = null): array
    {
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate]);

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        return [
            'completed' => (clone $orderQuery)->where('payment_status', 'paid')->count(),
            'pending'   => (clone $orderQuery)->where('payment_status', 'pending')->count(),
            'cancelled' => (clone $orderQuery)->where('payment_status', 'failed')->count(),
        ];
    }

    /**
     * Calcular los ingresos de E-Commerce con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return string
     */
    public function ecommerceIncomes(string $startDate, string $endDate, int $storeId = null): string
    {
        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('origin', 'ecommerce');
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        $orders          = $query->get();
        $totalPaidOrders = 0;
        foreach ($orders as $order) {
            $totalPaidOrders += $this->convertOrderAmount($order, $order->total);
        }

        return number_format($totalPaidOrders, 0, ',', '.');
    }

    /**
     * Calcular los ingresos físicos con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return string
     */
    public function physicalIncomes(string $startDate, string $endDate, int $storeId = null): string
    {
        // Orders origin 'physical'
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('origin', 'physical');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $orders = $orderQuery->get();

        $totalPaid = 0;
        foreach ($orders as $order) {
            $totalPaid += $this->convertOrderAmount($order, $order->total);
        }

        return number_format($totalPaid, 0, ',', '.');
    }

    /**
     * Calcular los ingresos totales con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return string
     */
    public function totalIncomes(string $startDate, string $endDate, int $storeId = null): string
    {
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $orders = $orderQuery->get();

        $totalPaid = 0;
        foreach ($orders as $order) {
            $totalPaid += $this->convertOrderAmount($order, $order->total);
        }

        return number_format($totalPaid, 0, ',', '.');
    }

    /**
     * Calcular la media mensual de ventas históricas.
     *
     * @param int|null $storeId
     * @return string
     */
    public function averageMonthlySales(int $storeId = null): string
    {
        // Consulta para obtener las ventas mensuales de la tabla Order
        $orderQuery = Order::select(
            DB::raw('SUM(total) as total'),
            DB::raw('YEAR(date) as year'),
            DB::raw('MONTH(date) as month'),
            'currency'
        )
            ->where('payment_status', 'paid')
            ->groupBy(DB::raw('YEAR(date)'), DB::raw('MONTH(date)'));

        // Aplicar filtro por store_id si es proporcionado
        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        // Obtener ventas mensuales
        $monthlySales = $orderQuery
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Calcular promedio mensual
        if ($monthlySales->isEmpty()) {
            return '0';
        }

        $totalConverted = 0;
        $countMonths    = 0;

        $salesByMonth = $monthlySales->groupBy(function ($item) {
            return $item->year . '-' . $item->month;
        });

        foreach ($salesByMonth as $monthSales) {
            $monthTotal = 0;
            foreach ($monthSales as $sale) {
                // Pasar de dólares a pesos si es necesario
                if ($sale->currency === 'Dólar') {
                    $mockOrder             = new Order();
                    $mockOrder->created_at = Carbon::createFromDate($sale->year, $sale->month, 1);
                    $rate                  = $this->getHistoricalDollarRate($mockOrder);
                    $monthTotal += $sale->total * $rate['sell'];
                } else {
                    $monthTotal += $sale->total;
                }
            }
            $totalConverted += $monthTotal;
            $countMonths++;
        }

        $averageMonthlySales = $countMonths > 0 ? $totalConverted / $countMonths : 0;
        return number_format($averageMonthlySales, 0, ',', '.');
    }

    /**
     * Calcular el ticket medio con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return string
     */
    public function averageTicket(string $startDate, string $endDate, int $storeId = null): string
    {
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $orders = $orderQuery->get();

        $totalPaid = 0;
        foreach ($orders as $order) {
            $totalPaid += $this->convertOrderAmount($order, $order->total);
        }

        $count = $orders->count();

        if ($count > 0) {
            return number_format($totalPaid / $count, 0, ',', '.');
        }

        return 'N/A';
    }

    /**
     * Obtener datos de ingresos con filtro de fecha y local.
     *
     * Este método obtiene los datos de ingresos agrupados por año, mes, día o hora dependiendo del período seleccionado.
     *
     * @param string $startDate La fecha de inicio del rango a consultar.
     * @param string $endDate La fecha de fin del rango a consultar.
     * @param int|null $storeId El ID del local para filtrar los resultados. Si es null, se consideran todos los locales.
     * @param string $period El período de agrupación de los resultados ('today', 'week', 'month', 'year', 'always').
     * @return EloquentCollection La colección de resultados agrupados.
     */
    public function getIncomeData(string $startDate, string $endDate, int $storeId = null, string $period = 'month'): EloquentCollection
    {
        // Selección y agrupación de campos según el periodo
        switch ($period) {
            case 'today':
                $groupBy = ['year', 'month', 'day', 'hour'];
                $select  = [
                    'total', 'currency',
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                    DB::raw('DAY(date) as day'),
                    DB::raw('HOUR(time) as hour'),
                ];
                break;

            case 'week':
            case 'month':
                $groupBy = ['year', 'month', 'day'];
                $select  = [
                    'total', 'currency',
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                    DB::raw('DAY(date) as day'),
                ];
                break;

            case 'year':
            case 'always':
            default:
                $groupBy = ['year', 'month'];
                $select  = [
                    'total', 'currency',
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                ];
                break;
        }

        // Consulta sin GROUP_CONCAT para obtener los datos sin concatenar
        $orderQuery = Order::select($select)
            ->where('payment_status', 'paid')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('time');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        // Obtener datos sin concatenar
        $orders = $orderQuery->get();

        // Agrupar en PHP en lugar de MySQL
        $groupedResults = [];

        foreach ($orders as $row) {
            // Crear clave de agrupación dinámica
            $key = implode('-', array_map(fn($field) => $row->{$field}, $groupBy));

            if (! isset($groupedResults[$key])) {
                $groupedResults[$key] = [
                    'amounts' => [],
                    'year'    => $row->year,
                    'month'   => $row->month,
                ];

                // Agregar los campos opcionales según el periodo
                foreach ($groupBy as $field) {
                    $groupedResults[$key][$field] = $row->{$field} ?? null;
                }
            }

            // Agregar `total|currency` en PHP sin GROUP_CONCAT
            $groupedResults[$key]['amounts'][] = [
                'total'    => floatval($row->total),
                'currency' => $row->currency,
            ];
        }

        // Convertir totales según moneda
        foreach ($groupedResults as &$group) {
            $group['total'] = array_sum(array_map(function ($amount) {
                $mockOrder             = new Order();
                $mockOrder->currency   = $amount['currency'];
                $mockOrder->created_at = now(); // Fallback con fecha actual
                return $this->convertOrderAmount($mockOrder, $amount['total']);
            }, $group['amounts']));
        }

        // Convertir a EloquentCollection antes de retornar
        return new EloquentCollection(array_values($groupedResults));
    }

    /**
     * Rellenar los campos faltantes en los resultados de ingresos.
     *
     * Este método recorre un rango de fechas y verifica si para cada fecha existe un registro en la colección de resultados.
     * Si no existe, rellena los datos faltantes con 0.
     *
     * @param EloquentCollection $results La colección original de resultados.
     * @param string $startDate La fecha de inicio del rango a consultar.
     * @param string $endDate La fecha de fin del rango a consultar.
     * @param array $selectFields Los campos seleccionados para el período actual.
     * @return EloquentCollection La colección de resultados con los campos faltantes rellenados.
     */
    private function fillMissingData(EloquentCollection $results, string $startDate, string $endDate, array $selectFields): EloquentCollection
    {
        $filledResults = collect();

        // Crear un rango de fechas basado en el inicio y el final
        $period = Carbon::parse($startDate)->daysUntil($endDate);

        foreach ($period as $date) {
            // Busca si existe un registro que coincida con el grupo seleccionado (mes, día, hora, etc.)
            $matchingResult = $results->first(function ($item) use ($date, $selectFields) {
                foreach ($selectFields as $field) {
                    // Compara las propiedades según el campo correspondiente
                    switch ($field) {
                        case 'year':
                            if ($item->year != $date->year) {
                                return false;
                            }

                            break;
                        case 'month':
                            if ($item->month != $date->month) {
                                return false;
                            }

                            break;
                        case 'day':
                            if ($item->day != $date->day) {
                                return false;
                            }

                            break;
                        case 'hour':
                            if ($item->hour != $date->hour) {
                                return false;
                            }

                            break;
                    }
                }
                return true;
            });

            // Si no se encuentra ningún resultado, se rellena con 0
            $filledResults->push([
                'year'  => $date->year,
                'month' => $date->month,
                'day'   => in_array('day', $selectFields) ? $date->day : null,
                'hour'  => in_array('hour', $selectFields) ? $date->hour : null,
                'total' => $matchingResult ? $matchingResult->total : 0,
            ]);
        }

        return new EloquentCollection($filledResults);
    }

    /**
     * Obtener ventas por local en porcentaje para gráfica de torta.
     *
     * @param int|null $storeId
     * @return array
     */
    public function getSalesByStoreData(int $storeId = null): array
    {
        $stores = Store::all();

        $allOrders = Order::where('payment_status', 'paid')
            ->when($storeId, function ($query) use ($storeId) {
                return $query->where('store_id', $storeId);
            })
            ->get();

        // Pasar de dolar a peso uruguayo.
        $totalPaidOrders = 0;
        foreach ($allOrders as $order) {
            $totalPaidOrders += $this->convertOrderAmount($order, $order->total);
        }

        $data = [];

        foreach ($stores as $store) {
            $storeOrders = $allOrders->where('store_id', $store->id);

            // Pasar de dolar a peso uruguayo.
            $storeTotalOrders = 0;
            foreach ($storeOrders as $order) {
                $storeTotalOrders += $this->convertOrderAmount($order, $order->total);
            }

            // Calcular porcentaje.
            $percent = $totalPaidOrders > 0 ? ($storeTotalOrders / $totalPaidOrders) * 100 : 0;

            $data[] = [
                'store'   => $store->name,
                'percent' => number_format($percent, 2, ',', '.'),
            ];
        }

        return $data;
    }

    /**
     * Obtener porcentaje de ventas por local para tabla con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSalesPercentByStore(string $startDate, string $endDate): array
    {
        $allOrders = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->get();

        $totalPaidOrders = 0;
        foreach ($allOrders as $order) {
            $totalPaidOrders += $this->convertOrderAmount($order, $order->total);
        }

        $stores = Store::with(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])
                ->where('payment_status', 'paid');
        }])->get();

        $data = [];
        foreach ($stores as $store) {
            $storeTotal = 0;
            foreach ($store->orders as $order) {
                $storeTotal += $this->convertOrderAmount($order, $order->total);
            }

            $percent = $totalPaidOrders > 0 ? ($storeTotal / $totalPaidOrders) * 100 : 0;

            $data[] = [
                'store'      => $store->name,
                'percent'    => round($percent, 2),
                'storeTotal' => $storeTotal, // Raw number, not formatted string
            ];
        }

        usort($data, function ($a, $b) {
            return $b['storeTotal'] <=> $a['storeTotal'];
        });

        return $data;
    }

    /**
     * Obtener porcentaje de ventas por producto para tabla con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getSalesPercentByProduct(string $startDate, string $endDate, int $storeId = null): array
    {
        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $orders       = $query->get();
        $productSales = [];

        foreach ($orders as $order) {
            $products = json_decode($order->products, true);

            if (is_array($products) && count($products) > 0) {
                $subtotal          = 0;
                $convertedProducts = [];

                foreach ($products as $product) {
                    if (is_array($product) && isset($product['name'], $product['price'], $product['quantity'])) {
                        $productPrice = $product['price'];

                        if (isset($product['currency']) && $product['currency'] === 'Dólar') {
                            $rate = $this->getHistoricalDollarRate($order);
                            $productPrice *= $rate['sell'];
                        }

                        $convertedProducts[] = [
                            'name'     => $product['name'],
                            'price'    => $productPrice,
                            'quantity' => $product['quantity'],
                        ];

                        $subtotal += ($productPrice * $product['quantity']);
                    }
                }

                $total = $this->convertOrderAmount($order, $order->total);

                foreach ($convertedProducts as $product) {
                    if (! isset($productSales[$product['name']])) {
                        $productSales[$product['name']] = [
                            'total' => 0,
                            'count' => 0,
                        ];
                    }

                    $productSubtotal             = $product['price'] * $product['quantity'];
                    $productPercentageOfSubtotal = $subtotal > 0 ? $productSubtotal / $subtotal : 0;
                    $productAdjustedTotal        = $productPercentageOfSubtotal * $total;

                    $productSales[$product['name']]['total'] += $productAdjustedTotal;
                    $productSales[$product['name']]['count'] += $product['quantity'];
                }
            }
        }

        $totalSales = array_sum(array_map(function ($product) {
            return $product['total'];
        }, $productSales));

        $data = [];
        foreach ($productSales as $name => $info) {
            $percent = $totalSales > 0 ? ($info['total'] / $totalSales) * 100 : 0;
            $data[]  = [
                'product'      => $name,
                'percent'      => round($percent, 2),
                'productTotal' => $info['total'],
            ];
        }

        usort($data, function ($a, $b) {
            return $b['productTotal'] <=> $a['productTotal'];
        });

        return $data;
    }

    /**
     * Obtener datos de uso de cupones con el total descontado y ordenarlos.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getCouponsUsage(string $startDate, string $endDate, int $storeId = null): array
    {
        $query = Coupon::with(['orders' => function ($query) use ($startDate, $endDate, $storeId) {
            $query->whereBetween('date', [$startDate, $endDate]);
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
        }]);
        $coupons = $query->get();

        $data = [];

        foreach ($coupons as $coupon) {
            $totalDiscount = $coupon->orders->sum('coupon_amount');
            $data[]        = [
                'code'           => $coupon->code,
                'total_discount' => $totalDiscount,
                'uses'           => $coupon->orders->count(),
            ];
        }

        usort($data, function ($a, $b) {
            return $b['total_discount'] <=> $a['total_discount'];
        });

        return $data;
    }

    /**
     * Obtener el promedio de ventas por hora para gráfica.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getAverageOrdersByHour(string $startDate = null, string $endDate = null, int $storeId = null): array
    {
        // Si hay un storeId definido, filtrar solo por ese storeId
        $stores = $storeId ? Store::where('id', $storeId)->get() : Store::all();
        $result = [];

        foreach ($stores as $store) {
            $orderQuery = Order::select(DB::raw('HOUR(time) as hour'), DB::raw('COUNT(*) as count'))
                ->where('payment_status', 'paid')
                ->where('store_id', $store->id)
                ->groupBy(DB::raw('HOUR(time)'));

            if ($startDate && $endDate) {
                $orderQuery->whereBetween('date', [$startDate, $endDate]);
            }

            $orders = $orderQuery->get();

            $hourlyData = array_fill(0, 24, 0);

            foreach ($orders as $order) {
                $hourlyData[$order->hour] += $order->count;
            }

            $result[] = [
                'store' => $store->name,
                'data'  => $hourlyData,
            ];
        }

        return $result;
    }

    /**
     * Obtiene los datos de ventas por categoría para tabla comparativa.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getSalesPercentByCategory(string $startDate, string $endDate, int $storeId = null): array
    {
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $orders        = $orderQuery->get();
        $categorySales = [];

        foreach ($orders as $order) {
            $products = json_decode($order->products, true);

            if (! is_array($products) || empty($products)) {
                continue;
            }

            $orderTotal = $order->total;
            if ($order->currency === 'Dólar') {
                $rate = $this->getHistoricalDollarRate($order);
                $orderTotal *= $rate['sell'];
            }

            $subtotal = 0;

            foreach ($products as $product) {
                if (! isset($product['id'], $product['price'], $product['quantity'])) {
                    continue;
                }

                $dbProduct = Product::with('categories')->find($product['id']);
                if (! $dbProduct || $dbProduct->categories->isEmpty()) {
                    continue;
                }

                $productPrice = $product['price'];
                if (isset($product['currency']) && $product['currency'] === 'Dólar') {
                    $rate = $this->getHistoricalDollarRate($order);
                    $productPrice *= $rate['sell'];
                }

                $subtotal += ($productPrice * $product['quantity']);

                foreach ($dbProduct->categories as $category) {
                    if (! isset($categorySales[$category->id])) {
                        $categorySales[$category->id] = [
                            'total'         => 0,
                            'count'         => 0,
                            'category_name' => $category->name ?? 'Sin categoría',
                        ];
                    }

                    $productTotal  = $productPrice * $product['quantity'];
                    $proportion    = $subtotal > 0 ? $productTotal / $subtotal : 0;
                    $adjustedTotal = $proportion * $orderTotal;

                    $categorySales[$category->id]['total'] += $adjustedTotal;
                    $categorySales[$category->id]['count'] += $product['quantity'];
                }
            }
        }

        $totalSales = array_sum(array_map(function ($category) {
            return $category['total'];
        }, $categorySales));

        $data = [];
        foreach ($categorySales as $category) {
            $percent = $totalSales > 0 ? ($category['total'] / $totalSales) * 100 : 0;
            $data[]  = [
                'category'      => $category['category_name'],
                'percent'       => round($percent, 2),
                'categoryTotal' => $category['total'],
            ];
        }

        usort($data, function ($a, $b) {
            return $b['categoryTotal'] <=> $a['categoryTotal'];
        });

        return $data;
    }

    /**
     * Obtener datos de métodos de pago para gráfica de torta.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getPaymentMethodsData(string $startDate, string $endDate, int $storeId = null): array
    {
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $orders = $orderQuery->get();

        $paymentMethods = [
            'Crédito'  => 0,
            'Débito'   => 0,
            'Efectivo' => 0,
            'Otro'     => 0,
        ];

        foreach ($orders as $order) {
            $convertedAmount = $this->convertOrderAmount($order, $order->total);

            $method = $order->payment_method;
            if ($method === 'credit') {
                $paymentMethods['Crédito'] += $convertedAmount;
            } elseif ($method === 'debit') {
                $paymentMethods['Débito'] += $convertedAmount;
            } elseif ($method === 'cash') {
                $paymentMethods['Efectivo'] += $convertedAmount;
            } else {
                $paymentMethods['Otro'] += $convertedAmount;
            }
        }

        $total = array_sum($paymentMethods);

        foreach ($paymentMethods as $method => $amount) {
            $paymentMethods[$method] = [
                'amount'  => $amount,
                'percent' => $total > 0 ? ($amount / $total) * 100 : 0,
            ];
        }

        return $paymentMethods;
    }

    /**
     * Obtener ventas por vendedor para gráfica de barras con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getSalesBySellerData(string $startDate, string $endDate, int $storeId = null): array
    {
        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $salesBySeller = $query->join('cash_register_logs', 'orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->join('users', 'cash_registers.user_id', '=', 'users.id')
            ->select(
                'users.name as seller',
                'orders.currency',
                'orders.total',
                'orders.created_at'
            )
            ->get()
            ->groupBy('seller')
            ->map(function ($sellerOrders) {
                $totalSales = 0;
                foreach ($sellerOrders as $order) {
                    if ($order->currency === 'Dólar') {
                        $rate = $this->getHistoricalDollarRate($order);
                        $totalSales += $order->total * $rate['sell'];
                    } else {
                        $totalSales += $order->total;
                    }
                }
                return [
                    'seller'     => $sellerOrders->first()->seller,
                    'totalSales' => $totalSales,
                ];
            })
            ->sortByDesc('totalSales')
            ->values()
            ->toArray();

        return $salesBySeller;
    }

    /*
    * Obtiene la tasa de cambio del dólar utilizada para la órden.
    *
    * @return
    */
    public function getHistoricalDollarRate(Order $order)
    {
        $dollar = CurrencyRate::where('name', 'Dólar')->first();

        $rate = CurrencyRateHistory::where('currency_rate_id', $dollar->id)
            ->where('created_at', '<=', $order->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $rate) {
            $rate = CurrencyRateHistory::where('currency_rate_id', $dollar->id)
                ->where('created_at', '>', $order->created_at)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        return [
            'date' => $rate->created_at->format('Y-m-d H:i:s'),
            'sell' => $rate->sell,
        ];
    }

    /**
     * Convierte el monto de la orden a dolar.
     *
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
