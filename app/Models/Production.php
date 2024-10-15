<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'flavor_id',
        'quantity'
    ];

    /**
     * Obtiene el producto de la producción.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtiene el sabor de la producción.
     *
     * @return BelongsTo
     */
    public function flavor(): BelongsTo
    {
        return $this->belongsTo(Flavor::class);
    }
}
