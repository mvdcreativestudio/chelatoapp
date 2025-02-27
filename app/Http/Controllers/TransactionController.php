<?php

namespace App\Http\Controllers;

use App\Repositories\TransactionRepository;
use App\Models\Store;
use App\Models\Order;

class TransactionController extends Controller
{
    protected $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function index()
    {
        $stores = Store::all(); // Obtener todas las tiendas
        $formattedTransactions = $this->transactionRepository->getFormattedTransactions(); // Transacciones formateadas
        $transactionType = config('FiservResponses.fiservResponsesMapping.transactionType');
        $posResponseCode = config('FiservResponses.fiservResponsesMapping.posResponseCode');
        $posResponseCodeExtension = config('FiservResponses.fiservResponsesMapping.posResponseCodeExtension');
        $transactionState = config('FiservResponses.fiservResponsesMapping.transactionState');
        $aquirer = config('FiservResponses.fiservResponsesMapping.aquirer');
        $currency = config('FiservResponses.fiservResponsesMapping.currency');
        $issuer = config('FiservResponses.fiservResponsesMapping.issuer');
        $orders = Order::all();

        return view('transactions.index', compact(
            'stores',
            'formattedTransactions',
            'transactionType',
            'posResponseCode',
            'posResponseCodeExtension',
            'transactionState',
            'aquirer',
            'currency',
            'issuer',
            'orders'
        ));
    }
}


