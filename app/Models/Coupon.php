<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'amount',
        'product_categories',
        'products',
        'init_date',
        'due_date',
        'creator_id',
        'status'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

      /**
     * Relación con las órdenes que utilizan este cupón.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relación con los productos excluidos de este cupón.
     *
     * @return BelongsToMany
     */
    public function excludedProducts()
    {
        return $this->belongsToMany(Product::class, 'coupon_exclude_products');
    }

    /**
     * Relación con las categorías excluidas de este cupón.
     *
     * @return BelongsToMany
     */
    public function excludedCategories()
    {
        return $this->belongsToMany(ProductCategory::class, 'coupon_exclude_categories', 'coupon_id', 'category_id');
    }


}
