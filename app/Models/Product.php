<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'max_flavors',
        'old_price',
        'price',
        'discount',
        'categories',
        'tags',
        'atributtes',
        'variations',
        'image',
        'store_id',
        'status',
        'stock'
      ];


    // Relación con Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Relación con ProductCategory
    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'category_product', 'product_id', 'category_id');
    }

    // Relación con Orders
    public function orders()
    {
    return $this->belongsToMany(Order::class, 'order_products')
                ->withPivot('quantity', 'price')
                ->withTimestamps();
    }

    public function flavors()
    {
        return $this->belongsToMany(Flavor::class, 'product_flavor')->withTimestamps();
    }

}
