<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['check_permission:access_stock-movements']);
    }

    public function index(Request $request)
    {
        $query = StockMovement::with(['user'])
            ->orderBy('created_at', 'desc');

        // Filtrar por tienda según permisos del usuario
        if (!Auth::user()->can('access_global_products')) {
            $userStoreId = Auth::user()->store_id;
            $query->where(function ($q) use ($userStoreId) {
                $q->whereHasMorph('product', [\App\Models\Product::class], function ($q) use ($userStoreId) {
                    $q->where('store_id', $userStoreId);
                })->orWhereHasMorph('product', [\App\Models\CompositeProduct::class], function ($q) use ($userStoreId) {
                    $q->where('store_id', $userStoreId);
                });
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHasMorph('product', [\App\Models\Product::class, \App\Models\CompositeProduct::class], function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $movements = $query->paginate(25)->withQueryString();

        // Cargar nombres de productos en una sola consulta para evitar N+1
        $movements->getCollection()->transform(function ($movement) {
            if ($movement->product_type === 'App\\Models\\Product') {
                $movement->product_name = \App\Models\Product::where('id', $movement->product_id)->value('name') ?? 'Producto eliminado';
            } elseif ($movement->product_type === 'App\\Models\\CompositeProduct') {
                $movement->product_name = \App\Models\CompositeProduct::where('id', $movement->product_id)->value('name') ?? 'Producto eliminado';
            } else {
                $movement->product_name = 'Desconocido';
            }
            return $movement;
        });

        return view('content.e-commerce.backoffice.products.stock-movements', compact('movements'));
    }
}
