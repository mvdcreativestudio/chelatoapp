<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    public function getAllTransactions()
    {
        return Transaction::orderBy('created_at', 'desc')->get();
    }
}
