<?php

namespace App\Repositories;

use App\Helpers\Helpers;
use App\Models\CompositeProduct;
use App\Models\CompositeProductDetail;
use App\Models\Product;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class CompositeProductRepository
{
    /**
     * Obtiene todos los productos compuestos y estadísticas necesarias.
     *
     * @return array
     */
    public function getAllCompositeProducts(): array
    {
        // Verificar si el usuario tiene permiso para ver todos los productos compuestos de la tienda
        if (Auth::user()->can('view_all_composite-products')) {
            // Si tiene el permiso, obtenemos todos los productos compuestos
            $compositeProducts = CompositeProduct::all();
        } else {
            // Si no tiene el permiso, solo obtenemos los productos compuestos de su store_id
            $compositeProducts = CompositeProduct::where('store_id', Auth::user()->store_id)->get();
        }

        // Calcular las estadísticas basadas en los productos compuestos filtrados
        $totalProducts = $compositeProducts->count();
        $totalPrice = $compositeProducts->sum('price');
        $totalRecommendedPrice = $compositeProducts->sum('recommended_price');

        return compact('compositeProducts', 'totalProducts', 'totalPrice', 'totalRecommendedPrice');
    }

    /**
     * Muestra el formulario para crear un nuevo producto compuesto.
     *
     * @return array
     */
    public function create(): array
    {
        $stores = $this->getAllStores();
        $products = $this->getAllProducts();
        return compact('stores', 'products');
    }

    /**
     * Almacena un nuevo producto compuesto en la base de datos.
     *
     * @param  array  $data
     * @param  array  $productIds
     * @return CompositeProduct
     */
    public function store(array $data): CompositeProduct
    {
        DB::beginTransaction();

        try {
            // Crear el nuevo producto compuesto y asignar los datos
            $compositeProduct = new CompositeProduct();
            $compositeProduct->fill($data);

            // Guardar el producto compuesto en la base de datos
            $compositeProduct->save();

            // Obtener el stock inicial del producto compuesto
            $initialStock = $data['stock']; // Supongo que en el formulario envías un valor de stock


            // Recorrer los productos individuales que componen el producto compuesto
            foreach ($data['products'] as $productData) {
                $productId = $productData['product_id'];
                $quantityInComposite = $productData['quantity']; // Cantidad de este producto en el compuesto

                // Crear la relación en la tabla de detalles
                CompositeProductDetail::create([
                    'composite_product_id' => $compositeProduct->id,
                    'product_id' => $productId,
                    'quantity_composite_product' => $quantityInComposite,
                ]);

                // Obtener el producto individual para restar su stock
                $product = Product::find($productId);

                // Calcular la cantidad total a descontar de este producto
                $totalQuantityToDeduct = $initialStock * $quantityInComposite;

                // Verificar si el producto tiene suficiente stock
                if ($product->stock < $totalQuantityToDeduct) {
                    throw new Exception("No hay suficiente stock para el producto: {$product->name}");
                }

                // Restar el stock del producto individual
                $product->stock -= $totalQuantityToDeduct;
                $product->save();
            }

            DB::commit();
            return $compositeProduct;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene un producto compuesto específico por su ID.
     *
     * @param int $productId
     * @return array
     */
    public function getCompositeProductById(int $productId): array
    {
        $compositeProduct = CompositeProduct::findOrFail($productId)->load('details');
        $stores = $this->getAllStores();
        $products = $this->getAllProducts();
        $compositeProduct->product_ids = $compositeProduct->details->pluck('product_id')->toArray();
        return compact('compositeProduct', 'stores', 'products');
    }

    /**
     * Actualiza un producto compuesto específico en la base de datos.
     *
     * @param CompositeProduct $compositeProduct
     * @param array $data
     * @param array $productIds
     * @return CompositeProduct
     */
    public function update(CompositeProduct $compositeProduct, array $data): CompositeProduct
    {
        DB::beginTransaction();

        try {
            // Actualizar los datos del producto compuesto
            $compositeProduct->fill($data);
            $compositeProduct->save();

            // Obtener los IDs de los productos ya existentes en el producto compuesto
            $existingProducts = CompositeProductDetail::where('composite_product_id', $compositeProduct->id)
                ->get()
                ->keyBy('product_id');

            // Recorrer los productos recibidos en la solicitud
            foreach ($data['products'] as $product) {
                $productId = $product['product_id'];
                $quantity = $product['quantity'];

                // Si el producto ya existe en los detalles, actualizar la cantidad
                if ($existingProducts->has($productId)) {
                    $existingDetail = $existingProducts[$productId];
                    $existingDetail->update([
                        'quantity_composite_product' => $quantity,
                    ]);
                } else {
                    // Si el producto no existe, agregarlo como un nuevo detalle
                    CompositeProductDetail::create([
                        'composite_product_id' => $compositeProduct->id,
                        'product_id' => $productId,
                        'quantity_composite_product' => $quantity,
                    ]);
                }
            }

            // Eliminar los productos que ya no están en la lista actualizada
            $incomingProductIds = collect($data['products'])->pluck('product_id')->toArray();
            CompositeProductDetail::where('composite_product_id', $compositeProduct->id)
                ->whereNotIn('product_id', $incomingProductIds)
                ->delete();

            // Recalcular y actualizar el costo total sumando los precios de los productos individuales
            $compositeProduct->recommended_price = CompositeProductDetail::where('composite_product_id', $compositeProduct->id)
                ->join('products', 'composite_product_details.product_id', '=', 'products.id')
                ->sum(DB::raw('products.build_price * composite_product_details.quantity_composite_product'));

            $compositeProduct->save();

            DB::commit();
            return $compositeProduct;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un producto compuesto específico.
     *
     * @param int $productId
     * @return void
     */
    public function destroyCompositeProduct(int $productId): void
    {
        $compositeProduct = CompositeProduct::findOrFail($productId);
        $compositeProduct->delete();
    }

    /**
     * Eliminar varios productos compuestos.
     *
     * @param array $productIds
     * @return void
     */
    public function deleteMultipleCompositeProducts(array $productIds): void
    {
        CompositeProduct::whereIn('id', $productIds)->delete();
    }

    /**
     * Obtiene los productos compuestos para la DataTable.
     *
     * @return mixed
     */
    public function getCompositeProductsForDataTable(Request $request): mixed
    {
        $query = CompositeProduct::select([
            'composite_products.id',
            'composite_products.name',
            'composite_products.price',
            'composite_products.recommended_price',
            'composite_products.store_id',
            'composite_products.created_at',
            'stores.name as store_name',
        ])
            ->join('stores', 'composite_products.store_id', '=', 'stores.id')
            ->orderBy('composite_products.created_at', 'desc');

        // Verificar permisos del usuario
        if (!Auth::user()->can('view_all_composite-products')) {
            $query->where('composite_products.store_id', Auth::user()->store_id);
        }

        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('composite_products.created_at', [$startDate, $endDate]);
        }

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }

    /**
     * Obtiene todas las tiendas.
     *
     * @return mixed
     */
    public function getAllStores(): mixed
    {
        return Store::all();
    }

    /**
     * Obtiene todos los productos.
     *
     * @return mixed
     */
    public function getAllProducts(): mixed
    {
        return Product::whereNotNull('old_price')
                      ->where('is_trash', 0)
                      ->get();
    }
}
