<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'supplier_id', 'order_date', 'shipping_status', 'payment_status', 'total', 'payment', 'payment_method', 'notes'];

    /**
     * Obtiene los materiales primas asociados a la orden de compra.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class, 'supplier_order_raw_material')
            ->withPivot('quantity')
            ->withPivot('unit_cost')
            ->withPivot('total_cost')
            ->withTimestamps();
    }

    /**
     * Obtiene el proveedor asociado a la orden de compra.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    /**
     * Obtiene la tienda a la que pertenece la orden de compra.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

}
