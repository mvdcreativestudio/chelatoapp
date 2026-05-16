<?php

namespace App\Repositories;

use App\Http\Requests\StorePriceListRequest;
use App\Http\Requests\UpdatePriceListRequest;
use App\Models\PriceList;
use App\Models\PriceListProduct;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class PriceListRepository
{
    /**
     * Datos comunes para create/edit.
     */
    public function commonFormData(): array
    {
        if (Auth::user()->can('view_all_price-lists')) {
            $stores = Store::select('id', 'name')->get();
        } else {
            $stores = Store::select('id', 'name')->where('id', Auth::user()->store_id)->get();
        }

        return compact('stores');
    }

    /**
     * Crea una nueva lista de precios.
     */
    public function createPriceList(StorePriceListRequest $request): PriceList
    {
        return PriceList::create($request->only(['store_id', 'name', 'description', 'currency']));
    }

    /**
     * Obtiene una lista de precios por id, con su tienda y productos.
     */
    public function getPriceListById(int $id): PriceList
    {
        return PriceList::with(['store:id,name', 'products' => function ($q) {
            $q->select('products.id', 'products.name', 'products.sku', 'products.image', 'products.store_id', 'products.price');
        }])->findOrFail($id);
    }

    /**
     * Actualiza una lista de precios y, opcionalmente, los precios de sus productos.
     */
    public function updatePriceList(int $id, UpdatePriceListRequest $request): PriceList
    {
        $priceList = PriceList::findOrFail($id);

        $priceList->update($request->only(['name', 'description', 'currency']));

        // Procesar precios: { product_id => price }
        $prices = $request->input('prices', []);
        foreach ($prices as $productId => $price) {
            if ($price === null || $price === '') {
                // Si viene vacío, quitar el producto de la lista
                PriceListProduct::where('price_list_id', $priceList->id)
                    ->where('product_id', $productId)
                    ->delete();
                continue;
            }

            PriceListProduct::updateOrCreate(
                ['price_list_id' => $priceList->id, 'product_id' => $productId],
                ['price' => (float) $price]
            );
        }

        return $priceList;
    }

    /**
     * Elimina una lista de precios.
     */
    public function deletePriceList(int $id): bool
    {
        $priceList = PriceList::findOrFail($id);
        return (bool) $priceList->delete();
    }

    /**
     * Datos para el DataTable de listas de precios.
     */
    public function getForDataTable(Request $request): mixed
    {
        $query = PriceList::with('store:id,name')
            ->withCount('products')
            ->select(['id', 'store_id', 'name', 'description', 'currency', 'created_at']);

        if (!Auth::user()->can('view_all_price-lists')) {
            $query->where('store_id', Auth::user()->store_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return DataTables::of($query)
            ->addColumn('store_name', fn ($pl) => optional($pl->store)->name)
            ->make(true);
    }

    /**
     * Devuelve los productos de la tienda de la lista con su precio asignado (si tiene).
     */
    public function getProductsForPriceList(int $storeId, int $priceListId, ?string $search = null, string $filter = 'all'): array
    {
        $priceList = PriceList::findOrFail($priceListId);

        $query = Product::query()
            ->leftJoin('price_list_products', function ($join) use ($priceListId) {
                $join->on('products.id', '=', 'price_list_products.product_id')
                     ->where('price_list_products.price_list_id', '=', $priceListId);
            })
            ->where('products.store_id', $storeId)
            ->where('products.is_trash', '!=', 1)
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.image',
                'products.price as base_price',
                'price_list_products.price as list_price',
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', '%' . $search . '%')
                  ->orWhere('products.sku', 'like', '%' . $search . '%')
                  ->orWhere('products.bar_code', 'like', '%' . $search . '%');
            });
        }

        if ($filter === 'priced') {
            $query->whereNotNull('price_list_products.price');
        } elseif ($filter === 'unpriced') {
            $query->whereNull('price_list_products.price');
        }

        $products = $query->orderBy('products.name')->limit(500)->get();

        return [
            'products' => $products,
            'price_list_currency' => $priceList->currency,
        ];
    }

    /**
     * Devuelve un mapa { product_id => price } con los precios que un cliente tiene
     * vía su lista de precios. Si no tiene lista, devuelve array vacío.
     */
    public function getPriceMapForClient(int $clientId): array
    {
        $priceList = PriceList::whereHas('clients', function ($q) use ($clientId) {
            $q->where('clients.id', $clientId);
        })->with('products:id')->first();

        if (!$priceList) {
            return ['price_list_id' => null, 'currency' => null, 'prices' => []];
        }

        $prices = PriceListProduct::where('price_list_id', $priceList->id)
            ->pluck('price', 'product_id')
            ->toArray();

        return [
            'price_list_id' => $priceList->id,
            'currency' => $priceList->currency,
            'prices' => $prices,
        ];
    }
}
