<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;


class LandingRepository
{
    /**
     * Obtiene todos los productos que se deben mostrar en el catálogo junto con sus categorías.
     *
     * @return Collection
     */
    public function getProductsWithCategories(): Collection
    {
        return Product::with(['categories'])
            ->where('show_in_catalogue', 1)
            ->get();
    }

    /**
     * Obtiene un producto por su ID.
     * 
     * @param int $id
     * @return Product|null
     */
    public function getProductById($id): ?Product
    {
        return Product::with(['categories', 'features', 'sizes', 'colors', 'gallery'])->findOrFail($id);
    }

    /**
     * Obtiene todas las categorías de productos que tienen productos dentro.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategories()
    {
        return ProductCategory::has('products')->get();
    }

    /**
     * Filtra los productos por categoría.
     *
     * @param int|null $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function filterProductsByCategory(?int $categoryId = null): Collection
    {
        return $categoryId
            ? Product::whereHas('categories', function ($query) use ($categoryId) {
                $query->where('id', $categoryId);
            })->get()
            : Product::all();
    }
}
