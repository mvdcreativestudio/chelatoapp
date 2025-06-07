<?php

namespace App\Repositories;

use App\Models\PriceList;
use App\Models\PriceListProduct;

class PriceListRepository
{
    /**
     * Obtiene todas las listas de precios.
     *
     * @return mixed
     */
    public function getAll()
    {
        return PriceList::withCount('products')->get();
    }    

    /**
     * Obtiene las listas de precio según el ID de la Store.
     * 
     * @param int $storeId
     * @return mixed
     */
    public function getByStoreId(int $storeId)
    {
        return PriceList::where('store_id', $storeId)->withCount('products')->get();
    }

    /**
     * Crea una nueva lista de precios.
     *
     * @param array $data
     * @return PriceList
     */
    public function createPriceList(array $data): PriceList
    {
        return PriceList::create($data);
    }

    /**
     * Obtiene una lista de precios por su ID.
     *
     * @param int $id
     * @return PriceList
     */
    public function getPriceListById(int $id): PriceList
    {
        return PriceList::with('store')->findOrFail($id);
    }

    /**
     * Actualiza una lista de precios existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updatePriceList(int $id, array $data): PriceList
    {
        $priceList = PriceList::findOrFail($id);
        $priceList->update($data); // Realiza la actualización
        return $priceList; // Retorna el modelo actualizado
    }
    

    /**
     * Elimina una lista de precios por su ID.
     *
     * @param int $id
     * @return bool
     */
    public function deletePriceList(int $id): bool
    {
        $priceList = PriceList::findOrFail($id);
        return $priceList->delete();
    }

    /**
     * Agrega un producto a una lista de precios.
     * 
     * @param int $priceListId
     * @param int $productId
     * @param float $price
     * @return mixed
     */
    public function addProductToPriceList($priceListId, $productId, $price)
    {
        return PriceListProduct::updateOrCreate(
            ['price_list_id' => $priceListId, 'product_id' => $productId],
            ['price' => $price]
        );
    }

    

}
