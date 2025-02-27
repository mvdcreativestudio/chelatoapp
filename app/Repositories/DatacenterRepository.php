<?php

namespace App\Repositories;

use App\Models\Store;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Order;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use App\Models\PosOrder;
use Illuminate\Support\Facades\Log;
use App\Helpers\CompanySettingsHelper;



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
        $end = $endDate ? Carbon::parse($endDate) : $today;

        switch ($period) {
            case 'today':
                $start = $today->copy()->startOfDay();
                $end = $today->copy()->endOfDay();
                return [$start, $end];
            case 'week':
                return [$today->copy()->subDays(6)->startOfDay(), $today->endOfDay()];
            case 'month':
                $start = $today->copy()->startOfMonth();
                $end = $today->copy()->endOfMonth();
                return [$start, $end];
            case 'year':
                $start = $today->copy()->startOfYear();
                $end = $today->copy()->endOfYear();
                return [$start, $end];
            case 'always':
                $firstSale = Order::min('date') ?? PosOrder::min('date');
                $start = $firstSale ? Carbon::parse($firstSale)->startOfMonth() : Carbon::minValue();
                $end = $end ?? Carbon::maxValue();
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
            'pending' => (clone $orderQuery)->where('payment_status', 'pending')->count(),
            'cancelled' => (clone $orderQuery)->where('payment_status', 'failed')->count()
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
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();

        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('origin', 'ecommerce');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $totalPaidOrders = $query->sum($includeTaxes ? 'total' : 'subtotal');

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
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();

        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('origin', 'physical');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $totalPaidOrders = $query->sum($includeTaxes ? 'total' : 'subtotal');

        return number_format($totalPaidOrders, 0, ',', '.');
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
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();

        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $totalPaidOrders = $query->sum($includeTaxes ? 'total' : 'subtotal');

        return number_format($totalPaidOrders, 0, ',', '.');
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
            DB::raw('MONTH(date) as month')
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

        $totalSales = $monthlySales->sum('total');
        $countMonths = $monthlySales->count();
        $averageMonthlySales = $totalSales / $countMonths;

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
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();

        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $totalPaidOrders = $query->sum($includeTaxes ? 'total' : 'subtotal');
        $totalPaidOrdersCount = $query->count();

        return $totalPaidOrdersCount > 0 ? number_format($totalPaidOrders / $totalPaidOrdersCount, 0, ',', '.') : 'N/A';
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
        // Determinar si se deben incluir impuestos
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();
        $sumColumn = $includeTaxes ? 'total' : 'subtotal';

        // Selección y agrupación dinámica de campos según el periodo
        switch ($period) {
            case 'today':
                $groupBy = [DB::raw('YEAR(date)'), DB::raw('MONTH(date)'), DB::raw('DAY(date)'), DB::raw('HOUR(time)')];
                $selectFields = [$sumColumn, 'year', 'month', 'day', 'hour'];
                $select = [
                    DB::raw("SUM($sumColumn) as total"),
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                    DB::raw('DAY(date) as day'),
                    DB::raw('HOUR(time) as hour')
                ];
                break;
            case 'week':
            case 'month':
                $groupBy = [DB::raw('YEAR(date)'), DB::raw('MONTH(date)'), DB::raw('DAY(date)')];
                $selectFields = [$sumColumn, 'year', 'month', 'day'];
                $select = [
                    DB::raw("SUM($sumColumn) as total"),
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                    DB::raw('DAY(date) as day')
                ];
                break;
            case 'year':
            case 'always':
            default:
                $groupBy = [DB::raw('YEAR(date)'), DB::raw('MONTH(date)')];
                $selectFields = [$sumColumn, 'year', 'month'];
                $select = [
                    DB::raw("SUM($sumColumn) as total"),
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month')
                ];
                break;
        }

        // Consulta de ventas del módulo de e-commerce y ventas físicas
        $orderQuery = Order::select($select)
            ->where('payment_status', 'paid')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy($groupBy);

        // Aplicar filtro por store_id si se proporciona
        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        // Obtener los resultados de la consulta
        $results = $orderQuery->get();
        Log::info('Resultados de getIncomeData:', $results->toArray());

        // Agregar cualquier campo faltante al resultado final
        $filledResults = $this->fillMissingData($results, $startDate, $endDate, $selectFields);

        return new EloquentCollection($filledResults);
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
                            if ($item->year != $date->year) return false;
                            break;
                        case 'month':
                            if ($item->month != $date->month) return false;
                            break;
                        case 'day':
                            if ($item->day != $date->day) return false;
                            break;
                        case 'hour':
                            if ($item->hour != $date->hour) return false;
                            break;
                    }
                }
                return true;
            });

            // Si no se encuentra ningún resultado, se rellena con 0
            $filledResults->push([
                'year' => $date->year,
                'month' => $date->month,
                'day' => in_array('day', $selectFields) ? $date->day : null,
                'hour' => in_array('hour', $selectFields) ? $date->hour : null,
                'total' => $matchingResult ? $matchingResult->total : 0
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
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();
        $sumColumn = $includeTaxes ? 'total' : 'subtotal';

        $stores = Store::all();

        // Obtener el total de ventas pagadas
        $totalPaidOrders = Order::where('payment_status', 'paid')->sum($sumColumn);

        $data = [];

        foreach ($stores as $store) {
            // Obtener ventas de cada tienda
            $storeTotalOrders = Order::where('store_id', $store->id)
                ->where('payment_status', 'paid')
                ->sum($sumColumn);

            $percent = $totalPaidOrders > 0 ? ($storeTotalOrders / $totalPaidOrders) * 100 : 0;

            $data[] = [
                'store' => $store->name,
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
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();
        $sumColumn = $includeTaxes ? 'total' : 'subtotal';

        // Obtener el total de ventas pagadas en el rango de fechas
        $totalPaidOrders = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->sum($sumColumn);

        // Obtener todas las tiendas con sus ventas en el rango de fechas
        $stores = Store::with(['orders' => function ($query) use ($startDate, $endDate, $sumColumn) {
            $query->whereBetween('date', [$startDate, $endDate])
                ->where('payment_status', 'paid');
        }])->get();

        $data = [];
        foreach ($stores as $store) {
            // Calcular el total de ventas por tienda
            $storeTotal = $store->orders->sum($sumColumn);

            // Calcular el porcentaje de ventas por tienda
            $percent = $totalPaidOrders > 0 ? ($storeTotal / $totalPaidOrders) * 100 : 0;

            $data[] = [
                'store' => $store->name,
                'percent' => round($percent, 2),
                'storeTotal' => number_format($storeTotal, 0, ',', '.'),
            ];
        }

        // Ordenar los datos por el total de ventas en orden descendente
        usort($data, fn($a, $b) => $b['storeTotal'] <=> $a['storeTotal']);

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
        // Determinar si los reportes deben incluir impuestos
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();

        // Consulta de órdenes filtradas por fecha y tienda
        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $orders = $query->get();
        $productSales = [];

        foreach ($orders as $order) {
            $products = json_decode($order->products, true);
            if (!is_array($products) || count($products) === 0) {
                continue;
            }

            foreach ($products as $product) {
                if (!isset($product['name'], $product['price'], $product['quantity'], $product['base_price'])) {
                    continue;
                }

                $productName = $product['name'];

                // Verificar si la orden tiene un `tax_rate_id == 1` (sin impuestos)
                $isTaxExempt = isset($order->tax_rate_id) && $order->tax_rate_id == 1;

                // Si la orden está exenta de impuestos o `includeTaxes` es falso, usamos `base_price`
                $productPrice = ($isTaxExempt || !$includeTaxes) ? $product['base_price'] : $product['price'];

                $productTotal = $productPrice * $product['quantity'];

                // Acumular ventas y cantidad por producto
                if (!isset($productSales[$productName])) {
                    $productSales[$productName] = [
                        'total' => 0,
                        'count' => 0,
                    ];
                }

                $productSales[$productName]['total'] += $productTotal;
                $productSales[$productName]['count'] += $product['quantity'];
            }
        }

        // Calcular el total de todas las ventas
        $totalSales = array_sum(array_column($productSales, 'total'));

        $data = [];
        foreach ($productSales as $name => $info) {
            $percent = $totalSales > 0 ? ($info['total'] / $totalSales) * 100 : 0;
            $data[] = [
                'product' => $name,
                'percent' => round($percent, 2),
                'productTotal' => round($info['total'], 2),
            ];
        }

        // Ordenar los productos por total de ventas en orden descendente
        usort($data, fn($a, $b) => $b['productTotal'] <=> $a['productTotal']);

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
            $data[] = [
                'code' => $coupon->code,
                'total_discount' => $totalDiscount,
                'uses' => $coupon->orders->count()
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
                'data' => $hourlyData
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
        // Consulta a la tabla Order
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $orders = $orderQuery->get();


        $categorySales = [];

        // Procesar ventas de Order
        foreach ($orders as $order) {
            $products = json_decode($order->products, true);

            foreach ($products as $product) {
                if (!isset($product['category_id']) || !$product['category_id']) {
                    continue;
                }

                if (!isset($categorySales[$product['category_id']])) {
                    $categorySales[$product['category_id']] = [
                        'total' => 0,
                        'count' => 0,
                        'category_name' => ProductCategory::find($product['category_id'])->name ?? 'Sin categoría'
                    ];
                }

                $categorySales[$product['category_id']]['total'] += $product['price'] * $product['quantity'];
                $categorySales[$product['category_id']]['count'] += $product['quantity'];
            }
        }


        $totalSales = array_sum(array_map(function ($category) {
            return $category['total'];
        }, $categorySales));

        $data = [];
        foreach ($categorySales as $category) {
            $percent = $totalSales > 0 ? ($category['total'] / $totalSales) * 100 : 0;
            $data[] = [
                'category' => $category['category_name'],
                'percent' => round($percent, 2),
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
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();
        $sumColumn = $includeTaxes ? 'total' : 'subtotal';

        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $orders = $query->get();
        $paymentMethods = ['Crédito' => 0, 'Débito' => 0, 'Efectivo' => 0, 'Otro' => 0];

        foreach ($orders as $order) {
            $method = $order->payment_method;
            $paymentMethods[$method] = ($paymentMethods[$method] ?? 0) + $order->$sumColumn;
        }

        $total = array_sum($paymentMethods);

        foreach ($paymentMethods as $method => $amount) {
            $paymentMethods[$method] = [
                'amount' => $amount,
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
        // Determinar si los reportes deben incluir impuestos
        $includeTaxes = CompanySettingsHelper::shouldIncludeTaxes();
        $sumColumn = $includeTaxes ? 'total' : 'subtotal';

        // Filtrar por el rango de fechas y el estado de pago
        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        // Filtrar por el store_id si se proporciona
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        // Obtener el total de ventas por vendedor considerando la configuración de impuestos
        $salesBySeller = $query
            ->join('cash_register_logs', 'orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->join('users', 'cash_registers.user_id', '=', 'users.id')
            ->select('users.name as seller', DB::raw("SUM(orders.$sumColumn) as totalSales"))
            ->groupBy('users.name')
            ->orderByDesc('totalSales')
            ->get();

        // Convertir los datos a un array y asegurar que totalSales sea numérico
        return $salesBySeller->map(function ($item) {
            return [
                'seller' => $item->seller,
                'totalSales' => round((float) $item->totalSales, 2), // Asegurar número y redondeo a 2 decimales
            ];
        })->toArray();
    }


}
