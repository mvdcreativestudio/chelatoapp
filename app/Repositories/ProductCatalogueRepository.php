<?php

namespace App\Repositories;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Catalogue;
use Illuminate\Support\Facades\Log;


class ProductCatalogueRepository {

    /**
     * Obtiene productos filtrados por categoría.
     *
     * @param int|null $categoryId
     * @return array
     */
    public function getProductsByCategory(?int $categoryId = null): array {
        $query = Product::where('status', 1)
                        ->where('is_trash', '!=', 1);
    
        if ($categoryId) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('product_categories.id', $categoryId); // Especifica la tabla para evitar ambigüedad
            });
        }
    
        $products = $query->get()->toArray();
        
        // Registrar los productos en el log
        Log::info('Productos obtenidos:', ['products' => $products]);
    
        return $products;
    }
   

    /**
     * Obtiene todas las categorías con sus productos activos.
     *
     * @return array
     */
    public function getAllCategories(): array {
        return ProductCategory::with(['products' => function ($query) {
            $query->where('status', 1)->where('is_trash', '!=', 1);
        }])->has('products')->get()->toArray();
    }

    /**
     * Busca productos por nombre.
     * 
     * @param string $query
     * @return array
    */
    public function searchProducts(string $query): array
    {
        return Product::where('name', 'LIKE', '%' . $query . '%')
            ->where('status', 1)
            ->where('is_trash', '!=', 1)
            ->get()
        ->toArray();
    }   

}
