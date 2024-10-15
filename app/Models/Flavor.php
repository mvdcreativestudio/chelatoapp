<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Relación con las recetas.
     *
     * @return HasMany
    */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Obtiene la receta a la que pertenece el sabor.
     *
     * @return BelongsToMany
    */
    public function usedRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipes', 'flavor_id', 'used_flavor_id');
    }
}
