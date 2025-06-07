<?php

namespace App\Repositories;

use App\Models\Entry;
use App\Models\EntryDetail;
use Exception;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EntryDetailRepository
{
    /**
     * Obtiene los detalles de un asiento por su ID.
     *
     * @param int $entryId
     * @return Entry
     * @throws Exception
     */
    public function getEntryDetailsByEntryId(int $entryId): Entry
    {
        try {
            // Obtener el asiento con los detalles relacionados
            $entry = Entry::with('details.entryAccount')->findOrFail($entryId);
            // dd($entry);
            return $entry;
        } catch (Exception $e) {
            throw new Exception("Error al obtener los detalles del asiento con ID {$entryId}: " . $e->getMessage());
        }
    }

    /**
     * Almacena un nuevo detalle de asiento en la base de datos.
     *
     * @param  array  $data
     * @return EntryDetail
     */
    public function store(array $data): EntryDetail
    {
        DB::beginTransaction();

        try {
            $entryDetail = EntryDetail::create($data);
            DB::commit();
            return $entryDetail;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene un detalle de asiento específico por su ID.
     *
     * @param int $entryDetailId
     * @return EntryDetail
     */
    public function getEntryDetailById(int $entryDetailId): EntryDetail
    {
        return EntryDetail::findOrFail($entryDetailId);
    }

    /**
     * Actualiza un detalle de asiento específico en la base de datos.
     *
     * @param EntryDetail $entryDetail
     * @param array $data
     * @return EntryDetail
     */
    public function update(EntryDetail $entryDetail, array $data): EntryDetail
    {
        DB::beginTransaction();

        try {
            $entryDetail->update($data);
            DB::commit();
            return $entryDetail;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un detalle de asiento específico.
     *
     * @param int $entryDetailId
     * @return void
     */
    public function destroyEntryDetail(int $entryDetailId): void
    {
        $entryDetail = EntryDetail::findOrFail($entryDetailId);
        $entryDetail->delete();
    }

    /**
     * Elimina varios detalles de asiento.
     *
     * @param array $entryDetailIds
     * @return void
     */
    public function deleteMultipleEntryDetails(array $entryDetailIds): void
    {
        EntryDetail::whereIn('id', $entryDetailIds)->delete();
    }

    /**
     * Obtiene los detalles de un asiento para la DataTable.
     *
     * @param int $entryId
     * @return mixed
     */
    public function getEntryDetailsForDataTable(int $entryId): mixed
    {
        $query = EntryDetail::where('entry_id', $entryId)
            ->select(['id', 'entry_account_id', 'amount_debit', 'amount_credit']);

        return DataTables::of($query)
            ->addColumn('total_amount', function ($detail) {
                return number_format($detail->amount_debit + $detail->amount_credit, 2);
            })
            ->make(true);
    }

    public function getEntryWithDetails($entryId)
    {
        return Entry::with(['details', 'details.entryAccount', 'entryType', 'currency'])
            ->findOrFail($entryId);
    }
}
