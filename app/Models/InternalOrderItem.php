<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalOrderItem extends Model
{
    protected $fillable = [
        'internal_order_id',
        'product_id',
        'quantity',
        'received_quantity',
    ];

    public function internalOrder()
    {
        return $this->belongsTo(InternalOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
