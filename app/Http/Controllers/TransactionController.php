<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;
use App\Models\PosDevice;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $stores = Store::all(); // Obtener todas las tiendas
        $transactions = Transaction::all(); // Transacciones generales
        return view('transactions.index', compact('stores', 'transactions'));
    }
}
