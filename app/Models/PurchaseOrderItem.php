<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_orders_id',
        'raw_material_id',
        'product_id',
        'quantity',
        'currency',
        'unit_price',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_orders_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


    public function purchaseEntries()
    {
        return $this->hasMany(PurchaseEntry::class, 'purchase_order_items_id');
    }
}
