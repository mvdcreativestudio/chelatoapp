<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model {
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'product_id',
        'quantity',
        'price',
        'discount_type',
        'discount_price'
    ];

    /**
     * Obtiene el presupuesto asociado al item.
     *
     * @return BelongsTo
     */
    public function budget() {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Obtiene el producto asociado al item.
     *
     * @return BelongsTo
     */
    public function product() {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtiene el precio final del item.
     *
     * @return float
     */
    public function getFinalPriceAttribute() {
        if ($this->discount_type === 'Percentage') {
            return $this->price - ($this->price * $this->discount_price / 100);
        }
        return $this->price - $this->discount_price;
    }

    /**
     * Calcula el total del item.
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        $subtotal = $this->quantity * $this->price;
        if ($this->discount_type === 'Percentage') {
            return $subtotal * (1 - ($this->discount_price / 100));
        }
        return $subtotal - ($this->quantity * $this->discount_price);
    }
}
