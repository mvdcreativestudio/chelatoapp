<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Definir los atributos que se pueden asignar en masa
    protected $fillable = [
        'date',
        'client_id',
        'product_id',
        'store_id',
        'subtotal',
        'tax',
        'shipping',
        'coupon_id',
        'coupon_amount',
        'discount',
        'total',
        'payment_status',
        'shipping_status',
        'payment_method',
        'shipping_method',
        'shipping_tracking',
    ];

    // Relación con Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación con Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Relación con Coupon (si aplicable)
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

}
