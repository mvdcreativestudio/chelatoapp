<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'TransactionId',
        'STransactionId',
        'order_id',
        'formatted_data',
    ];

    protected $casts = [
        'formatted_data' => 'array', // Convertir automáticamente JSON a array
    ];
}
