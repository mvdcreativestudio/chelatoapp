<?php

namespace App\Repositories;

use App\Models\EntryAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EntryAccountRepository
{
    /**
     * Obtiene todas las cuentas contables.
     *
     * @return mixed
     */
    public function getAllEntryAccounts(): mixed
    {
        return EntryAccount::all();
    }

    /**
     * Almacena una nueva cuenta contable en la base de datos.
     *
     * @param array $data
     * @return EntryAccount
     */
    public function store(array $data): EntryAccount
    {
        DB::beginTransaction();

        try {
            // Crear la nueva cuenta contable
            $entryAccount = EntryAccount::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            DB::commit();
            return $entryAccount;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene una cuenta contable específica por su ID.
     *
     * @param int $entryAccountId
     * @return EntryAccount
     */
    public function getEntryAccountById(int $entryAccountId): EntryAccount
    {
        return EntryAccount::findOrFail($entryAccountId);
    }

    /**
     * Actualiza una cuenta contable específica en la base de datos.
     *
     * @param int $entryAccountId
     * @param array $data
     * @return EntryAccount
     */
    public function update(int $entryAccountId, array $data): EntryAccount
    {
        DB::beginTransaction();

        try {
            // Buscar y actualizar la cuenta contable
            $entryAccount = EntryAccount::findOrFail($entryAccountId);
            $entryAccount->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            DB::commit();
            return $entryAccount;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una cuenta contable específica.
     *
     * @param int $entryAccountId
     * @return void
     */
    public function destroyEntryAccount(int $entryAccountId): void
    {
        $entryAccount = EntryAccount::findOrFail($entryAccountId);
        $entryAccount->delete();
    }

    /**
     * Elimina varias cuentas contables.
     *
     * @param array $entryAccountIds
     * @return void
     */
    public function deleteMultipleEntryAccounts(array $entryAccountIds): void
    {
        DB::beginTransaction();

        try {
            // Eliminar las cuentas contables
            EntryAccount::whereIn('id', $entryAccountIds)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene las cuentas contables para la DataTable.
     *
     * @param Request $request
     * @return mixed
     */
    public function getEntryAccountsForDataTable(Request $request): mixed
    {
        $query = EntryAccount::select([
            'id',
            'code',
            'name',
            'description',
            'created_at',
        ])->orderBy('id', 'desc');

        return DataTables::of($query)->make(true);
    }
}
