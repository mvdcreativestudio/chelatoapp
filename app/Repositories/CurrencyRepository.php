<?php

namespace App\Repositories;

use App\Models\Currency;
use Exception;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CurrencyRepository
{
    /**
     * Obtiene todas las monedas.
     *
     * @return mixed
     */
    public function getAllCurrencies(): mixed
    {
        $currencies = Currency::all();
        $totalCurrencies = $currencies->count();
        return compact('currencies', 'totalCurrencies');
    }

    /**
     * Almacena una nueva moneda en la base de datos.
     *
     * @param array $data
     * @return Currency
     */
    public function store(array $data): Currency
    {
        DB::beginTransaction();

        try {
            $currency = Currency::create($data);
            DB::commit();
            return $currency;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene una moneda específica por su ID.
     *
     * @param int $currencyId
     * @return Currency
     */
    public function getCurrencyById(int $currencyId): Currency
    {
        return Currency::findOrFail($currencyId);
    }

    /**
     * Actualiza una moneda específica en la base de datos.
     *
     * @param Currency $currency
     * @param array $data
     * @return Currency
     */
    public function update(Currency $currency, array $data): Currency
    {
        DB::beginTransaction();
        try {
            $currency->update($data);
            DB::commit();
            return $currency;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una moneda específica.
     *
     * @param int $currencyId
     * @return void
     */
    public function destroyCurrency(int $currencyId): void
    {
        $currency = Currency::findOrFail($currencyId);
        $currency->delete();
    }

    /**
     * Elimina múltiples monedas.
     *
     * @param array $currencyIds
     * @return void
     */
    public function deleteMultipleCurrencies(array $currencyIds): void
    {
        Currency::whereIn('id', $currencyIds)->delete();
    }

    /**
     * Obtiene las monedas para la DataTable.
     *
     * @return mixed
     */
    public function getCurrenciesForDataTable(): mixed
    {
        $query = Currency::select([
            'id',
            'code',
            'symbol',
            'name',
            'exchange_rate',
            'created_at',
            'updated_at'
        ])->orderBy('id', 'desc');

        return DataTables::of($query)->make(true);
    }
}
