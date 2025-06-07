<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MercadoPagoAccountOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'status',
        'amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
