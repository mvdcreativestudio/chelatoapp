<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'parent_id',
        'slug',
        'status',
        'store_id'
      ];

    /**
     * Obtiene los productos asociados a la categoría.
     *
     * @return BelongsToMany
    */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id');
    }

    /*
    CHEQUEAR SI DESPUÉS NO FUNCIONA EL BELONGSTOMANY.
     public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    */
}
