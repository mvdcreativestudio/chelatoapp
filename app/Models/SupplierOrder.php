<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'order_date', 'shipping_status', 'payment_status', 'payment', 'notes'];

    /**
     * Obtiene los materiales primas asociados a la orden de compra.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class, 'supplier_order_raw_materials')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
