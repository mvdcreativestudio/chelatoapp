<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'TransactionId',
        'STransactionId',
        'order_id',
        'formatted_data',
        'type',
        'status',
    ];



    protected $casts = [
        'formatted_data' => 'array', // Convertir automáticamente JSON a array
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
