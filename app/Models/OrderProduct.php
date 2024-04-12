<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;


class OrderProduct extends Pivot
{
    // Especifica la tabla si no sigue la convención de nombres de Laravel
    protected $table = 'order_products';

    // Si tu tabla pivot no utiliza las marcas de tiempo automáticas de Laravel, establece esto en false
    public $timestamps = true;

    // Lista de propiedades que son asignables en masa
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * La relación con el modelo Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * La relación con el modelo Product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * La relación con el modelo Flavor a través de una tabla pivot adicional.
     * Suponiendo que tienes una tabla `order_product_flavor` para manejar esta relación.
     */
    public function flavors()
    {
        return $this->belongsToMany(Flavor::class, 'order_product_flavor', 'order_product_id', 'flavor_id');
    }
}
