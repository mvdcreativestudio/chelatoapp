<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompositeProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'price',
        'recommended_price',
        'image',
        'stock',
    ];

    /**
     * Obtiene la tienda a la que pertenece el producto compuesto.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene los detalles asociados al producto compuesto.
     *
     * @return HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(CompositeProductDetail::class);
    }
}
