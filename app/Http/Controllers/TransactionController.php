<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;
use App\Models\PosDevice;
use App\Models\Order;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $stores = Store::all(); // Obtener todas las tiendas
        $transactions = Transaction::orderBy('created_at', 'desc')->get(); // Ordenar transacciones por fecha descendente
        $transactionType = config('FiservResponses.fiservResponsesMapping.transactionType');
        $posResponseCode = config('FiservResponses.fiservResponsesMapping.posResponseCode');
        $posResponseCodeExtension = config('FiservResponses.fiservResponsesMapping.posResponseCodeExtension');
        $transactionState = config('FiservResponses.fiservResponsesMapping.transactionState');
        $aquirer = config('FiservResponses.fiservResponsesMapping.aquirer');
        $currency = config('FiservResponses.fiservResponsesMapping.currency');
        $issuer =config('FiservResponses.fiservResponsesMapping.issuer');

        $orders = Order::all();
        return view('transactions.index', compact('stores', 'transactions', 'transactionType', 'posResponseCode', 'posResponseCodeExtension', 'transactionState', 'aquirer', 'currency', 'issuer', 'orders'));
    }
}
