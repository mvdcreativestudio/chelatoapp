<?php

namespace App\Http\Controllers;

use App\Repositories\ProductCatalogueRepository;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Product;


class ProductCatalogueController extends Controller
{
    /**
     * El repositorio para las operaciones de catálogo de productos.
     *
     * @var ProductCatalogueRepository
     */
    protected $productCatalogueRepo;

    /**
     * Inyecta el repositorio en el controlador.
     *
     * @param ProductCatalogueRepository $productCatalogueRepo
     */
    public function __construct(ProductCatalogueRepository $productCatalogueRepo)
    {
        $this->productCatalogueRepo = $productCatalogueRepo;
    }

    /**
     * Muestra una lista de productos en el catálogo público con filtros por categorías.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $categoryId = $request->query('category');
        $products = $this->productCatalogueRepo->getProductsByCategory($categoryId);
        $categories = $this->productCatalogueRepo->getAllCategories();

        return view('catalogue.index', compact('products', 'categories'));
    }


    public function show(int $id): View {
        $product = Product::with('categories')->findOrFail($id);
        return view('catalogue.show-product', compact('product'));
    }
    

    public function search(Request $request)
    {
        $query = $request->input('query');
    
        if (empty($query)) {
            // Si no hay query, retorna todos los productos
            $products = Product::all();
        } else {
            // Filtra los productos según el query
            $products = Product::where('name', 'LIKE', '%' . $query . '%')->get();
        }
    
        return response()->json(['products' => $products]);
    }
    
}
