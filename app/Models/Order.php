<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Definir los atributos que se pueden asignar en masa
    protected $fillable = ['date', 'time', 'origin', 'client_id', 'store_id', 'subtotal', 'tax', 'shipping', 'coupon_id', 'coupon_amount', 'discount', 'total', 'payment_status', 'shipping_status', 'payment_method', 'shipping_method'];


    // Relación con Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación con Product
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
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
