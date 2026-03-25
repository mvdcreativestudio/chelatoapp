<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\StoreRepository;
use App\Models\Order;


class DashboardController extends Controller
{
    protected $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    public function index()
    {
        $stores = $this->storeRepository->getStoresWithStatus();
        $user = auth()->user();

        // Saludo dinámico según hora actual
        $currentHour = (int) now()->format('H');
        $greeting = match (true) {
            $currentHour >= 20 || $currentHour < 4 => 'Buenas noches',
            $currentHour < 12 => 'Buen dia',
            default => 'Buenas tardes',
        };

        return view('content.dashboard.index', compact('stores', 'user', 'greeting'));
    }

}
