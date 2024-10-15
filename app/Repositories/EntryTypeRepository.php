<?php

namespace App\Repositories;

use App\Models\EntryType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EntryTypeRepository
{
    /**
     * Obtiene todos los tipos de asientos.
     *
     * @return mixed
     */
    public function getAllEntryTypes(): mixed
    {
        return EntryType::all();
    }

    /**
     * Almacena un nuevo tipo de asiento en la base de datos.
     *
     * @param  array  $data
     * @return EntryType
     */
    public function store(array $data): EntryType
    {
        DB::beginTransaction();

        try {
            // Crear el nuevo tipo de asiento
            $entryType = EntryType::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            DB::commit();
            return $entryType;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene un tipo de asiento específico por su ID.
     *
     * @param int $entryTypeId
     * @return EntryType
     */
    public function getEntryTypeById(int $entryTypeId): EntryType
    {
        return EntryType::findOrFail($entryTypeId);
    }

    /**
     * Actualiza un tipo de asiento específico en la base de datos.
     *
     * @param int $entryTypeId
     * @param array $data
     * @return EntryType
     */
    public function update(int $entryTypeId, array $data): EntryType
    {
        DB::beginTransaction();

        try {
            // Buscar y actualizar el tipo de asiento
            $entryType = EntryType::findOrFail($entryTypeId);
            $entryType->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            DB::commit();
            return $entryType;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un tipo de asiento específico.
     *
     * @param int $entryTypeId
     * @return void
     */
    public function destroyEntryType(int $entryTypeId): void
    {
        $entryType = EntryType::findOrFail($entryTypeId);
        $entryType->delete();
    }

    /**
     * Elimina varios tipos de asientos.
     *
     * @param array $entryTypeIds
     * @return void
     */
    public function deleteMultipleEntryTypes(array $entryTypeIds): void
    {
        DB::beginTransaction();

        try {

            // Eliminar los tipos de asientos
            EntryType::whereIn('id', $entryTypeIds)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene los tipos de asientos para la DataTable.
     *
     * @param Request $request
     * @return mixed
     */
    public function getEntryTypesForDataTable(Request $request): mixed
    {
        $query = EntryType::select([
            'id',
            'name',
            'description',
            'created_at',
        ])->orderBy('id', 'desc');

        return DataTables::of($query)->make(true);
    }
}
