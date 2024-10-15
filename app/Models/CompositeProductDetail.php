<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompositeProductDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'composite_product_id',
        'product_id',
        'quantity_composite_product'
    ];

    /**
     * Obtiene el producto compuesto al que pertenece este detalle.
     *
     * @return BelongsTo
     */
    public function compositeProduct(): BelongsTo
    {
        return $this->belongsTo(CompositeProduct::class);
    }

    /**
     * Obtiene el producto individual asociado a este detalle.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
