<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Repositories\DatacenterRepository;

class DatacenterController extends Controller
{
    protected $datacenterRepo;

    public function __construct(DatacenterRepository $datacenterRepo)
    {
        $this->datacenterRepo = $datacenterRepo;
    }

    public function sales(Request $request)
    {
        // Filtra según el rango de fechas o periodo predeterminado (año, mes, semana, hoy, siempre)
        $period = $request->input('period', 'year');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $storeIdForView = $request->input('store_id'); // Este es para la vista

        // Obtén el rango de fechas basado en el periodo seleccionado
        list($startDate, $endDate) = $this->datacenterRepo->getDateRange($period, $startDate, $endDate);

        // Verifica si el usuario tiene el permiso para ver todas las empresa
        if (!auth()->user()->can('view_all_datacenter')) {
            // Si no tiene permiso, filtra por la empresa asignada al usuario
            $storeIdForView = auth()->user()->store_id;
        }

        // Filtra los datos con las fechas correspondientes y el filtro por local
        $storesCount = $this->datacenterRepo->countStores();
        $registredClients = $this->datacenterRepo->countClients($startDate, $endDate, $storeIdForView);
        $productsCount = $this->datacenterRepo->countProducts($startDate, $endDate, $storeIdForView);
        $categoriesCount = $this->datacenterRepo->countCategories($storeIdForView);
        $ordersCount = $this->datacenterRepo->countOrders($startDate, $endDate, $storeIdForView);

        // Ingresos
        $ecommerceIncomes = $this->datacenterRepo->ecommerceIncomes($startDate, $endDate, $storeIdForView);
        $physicalIncomes = $this->datacenterRepo->physicalIncomes($startDate, $endDate, $storeIdForView);
        $totalIncomes = $this->datacenterRepo->totalIncomes($startDate, $endDate, $storeIdForView);
        $averageMonthlySales = $this->datacenterRepo->averageMonthlySales($storeIdForView);

        // Ticket Medio
        $averageTicket = $this->datacenterRepo->averageTicket($startDate, $endDate, $storeIdForView);

        // Tabla de Locales / Productos / Cupones
        $salesByStore = $this->datacenterRepo->getSalesPercentByStore($startDate, $endDate);
        $salesByProduct = $this->datacenterRepo->getSalesPercentByProduct($startDate, $endDate, $storeIdForView);
        $salesByCategory = $this->datacenterRepo->getSalesPercentByCategory($startDate, $endDate, $storeIdForView);
        $couponUsage = $this->datacenterRepo->getCouponsUsage($startDate, $endDate, $storeIdForView);

        // Métodos de Pago
        $paymentMethods = $this->datacenterRepo->getPaymentMethodsData($startDate, $endDate, $storeIdForView);

        // Obtener todos los locales para el filtro por local
        $stores = Store::all();

        // Gráfica de promedio de ventas por hora
        $storeIdForChart = null;
        if (!auth()->user()->can('view_all_datacenter')) {
            $storeIdForChart = auth()->user()->store_id;
        }
        $averageOrdersByHour = $this->datacenterRepo->getAverageOrdersByHour($startDate, $endDate, $storeIdForChart);



        return view('datacenter.datacenter-sales', compact(
            'storesCount',
            'registredClients',
            'productsCount',
            'categoriesCount',
            'ordersCount',
            'ecommerceIncomes',
            'physicalIncomes',
            'totalIncomes',
            'averageTicket',
            'salesByStore',
            'averageMonthlySales',
            'salesByProduct',
            'salesByCategory',
            'couponUsage',
            'paymentMethods',
            'period',
            'startDate',
            'endDate',
            'storeIdForView', // Este es para la vista
            'stores', // Pasamos los locales a la vista para el filtro
            'averageOrdersByHour' // Pasamos los datos de la gráfica a la vista
        ));
    }





    // Gráfica de línea - GMV Mensual
    public function monthlyIncome(Request $request)
    {
        $period = $request->input('time_range', 'year');
        $storeId = $request->input('store_id');

        if (!auth()->user()->can('view_all_datacenter')) {
            $storeId = auth()->user()->store_id;
        }

        $startDate = null;
        $endDate = null;

        list($startDate, $endDate) = $this->datacenterRepo->getDateRange($period, $startDate, $endDate);

        $incomeData = $this->datacenterRepo->getIncomeData($startDate, $endDate, $storeId, $period);

        return response()->json($incomeData);
    }

    // Gráfica de torta - Ventas por Local en porcentaje
    public function salesByStore(Request $request)
    {
        $storeId = $request->input('store_id');
        $salesByStore = $this->datacenterRepo->getSalesByStoreData($storeId);
        return response()->json($salesByStore);
    }

    // Gráfica de torta - Métodos de Pago
    public function paymentMethodsData(Request $request)
    {
        $period = $request->input('period', 'year'); // Default is 'year'
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $storeId = $request->input('store_id');

        // Si no se pasa un store_id y el usuario no tiene permisos globales, usar el store_id del usuario.
        if (!$storeId && !auth()->user()->can('view_all_datacenter')) {
            $storeId = auth()->user()->store_id;
        }

        list($startDate, $endDate) = $this->datacenterRepo->getDateRange($period, $startDate, $endDate);

        $paymentMethods = $this->datacenterRepo->getPaymentMethodsData($startDate, $endDate, $storeId);
        return response()->json($paymentMethods);
    }

    public function salesBySellerData(Request $request)
    {
        $period = $request->input('period', 'year'); // Periodo por defecto es 'año'
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $storeId = $request->input('store_id');

        // Si no se pasa un store_id y el usuario no tiene permisos globales, usar el store_id del usuario.
        if (!$storeId && !auth()->user()->can('view_all_datacenter')) {
            $storeId = auth()->user()->store_id;
        }

        list($startDate, $endDate) = $this->datacenterRepo->getDateRange($period, $startDate, $endDate);

        $salesBySeller = $this->datacenterRepo->getSalesBySellerData($startDate, $endDate, $storeId);

        return response()->json($salesBySeller);
    }





}
