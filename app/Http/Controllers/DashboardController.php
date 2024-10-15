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

        return view('content.dashboard.index', compact('stores', 'user'));
    }

}
