<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\StoreRepository;
use App\Repositories\DashboardRepository;
use App\Models\Order;

class DashboardController extends Controller
{
    protected $storeRepository;
    protected $dashboardRepository;

    public function __construct(StoreRepository $storeRepository, DashboardRepository $dashboardRepository)
    {
        $this->storeRepository = $storeRepository;
        $this->dashboardRepository = $dashboardRepository;
    }

    /*
    * Muestra el dashboard
    * @return \Illuminate\View\View
    */
    public function index()
    {
        $stores = $this->storeRepository->getStoresWithStatus();

        $user = auth()->user();

        $products = $this->dashboardRepository->getTopSellingProducts();

        $amountOfOrders = $this->dashboardRepository->getAmountOfOrders();

        $unpaidExpenses = $this->dashboardRepository->getUnpaidExpensesSummary();

        $monthlyExpenses = $this->dashboardRepository->getMonthlyExpensesPaid();

        $dailyBalance = $this->dashboardRepository->getDailyBalance();


        return view('content.dashboard.index', compact('stores', 'user', 'products','amountOfOrders','unpaidExpenses','monthlyExpenses','dailyBalance'));
    }

    /*
    * Retorna los productos más vendidos
    * @param int $limit
    * @return array
    */
    public function monthlyIncomeDashboard(Request $request, $month = null)
    {
        $incomeData = $this->dashboardRepository->getMonthlyIncomeData($month);

        return response()->json($incomeData);
    }
}
