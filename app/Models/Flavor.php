<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Flavor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    /**
     * Obtiene los productos asociados al sabor.
     *
     * @return BelongsToMany
    */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_flavor')->withTimestamps();
    }

}
