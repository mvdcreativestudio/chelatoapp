<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;


class LandingRepository
{
    /**
     * Obtiene todos los productos junto con sus categorías y especificaciones.
     *
     * @return Collection
     */
    public function getProductsWithCategories(): Collection
    {
        return Product::with(['categories', 'specs'])->get();
    }
    

    /**
     * Obtiene un producto por su ID.
     * 
     * @param int $id
     * @return Product|null
     */
    public function getProductById($id): ?Product
    {
        return Product::with('categories')->findOrFail($id);
    }

}
