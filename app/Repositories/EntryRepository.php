<?php

namespace App\Repositories;

use App\Models\Currency;
use App\Models\Entry;
use App\Models\EntryAccount;
use App\Models\EntryDetail;
use App\Models\EntryType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EntryRepository
{
    /**
     * Obtiene todos los asientos y las estadísticas necesarias.
     *
     * @return array
     */
    public function getAllEntries(): array
    {
        $entries = Entry::all();
        $totalEntries = $entries->count();
        $totalDebit = $entries->sum('total_debit');
        $totalCredit = $entries->sum('total_credit');

        return compact('entries', 'totalEntries', 'totalDebit', 'totalCredit');
    }

    /**
     * Almacena un nuevo asiento en la base de datos.
     *
     * @param  array  $data
     * @return Entry
     */
    public function store(array $data): Entry
    {
        DB::beginTransaction();

        try {
            // Crear el asiento principal
            $entry = Entry::create([
                'entry_date' => $data['entry_date'],
                'entry_type_id' => $data['entry_type_id'],
                'concept' => $data['concept'],
                'currency_id' => $data['currency_id'],
            ]);

            // Guardar cada uno de los detalles
            foreach ($data['details'] as $detail) {
                $entry->details()->create([
                    'entry_account_id' => $detail['entry_account_id'],
                    'amount_debit' => $detail['amount_debit'],
                    'amount_credit' => $detail['amount_credit'],
                ]);
            }

            // Calcular si el asiento está balanceado
            $entry->is_balanced = $entry->calculateBalance();
            $entry->save();

            DB::commit();
            return $entry;
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene un asiento específico por su ID.
     *
     * @param int $entryId
     * @return Entry
     */
    public function getEntryById(int $entryId): Entry
    {
        return Entry::findOrFail($entryId)->load('details');
    }

    /**
     * Actualiza un asiento específico en la base de datos.
     *
     * @param Entry $entry
     * @param array $data
     * @return Entry
     */
    public function update(Entry $entry, array $data): Entry
    {
        DB::beginTransaction();

        try {
            // Actualizar los datos básicos del asiento
            $entry->update([
                'entry_date' => $data['entry_date'],
                'entry_type_id' => $data['entry_type_id'],
                'concept' => $data['concept'],
                'currency_id' => $data['currency_id'],
            ]);

            // Recoger los IDs de los detalles que se están enviando
            $detailIds = collect($data['details'])->pluck('id')->filter()->all();

            // Eliminar los detalles que no están en la lista recibida
            $entry->details()->whereNotIn('id', $detailIds)->delete();

            // Recorrer los detalles y actualizarlos o crearlos
            foreach ($data['details'] as $detail) {
                if (isset($detail['id']) && $detail['id']) {
                    // Si el detalle tiene un ID, actualizar el detalle existente
                    $entryDetail = $entry->details()->find($detail['id']);
                    if ($entryDetail) {
                        $entryDetail->update([
                            'entry_account_id' => $detail['entry_account_id'],
                            'amount_debit' => $detail['amount_debit'] ?? 0,
                            'amount_credit' => $detail['amount_credit'] ?? 0,
                        ]);
                    }
                } else {
                    // Si no tiene ID, crear un nuevo detalle
                    $entry->details()->create([
                        'entry_account_id' => $detail['entry_account_id'],
                        'amount_debit' => $detail['amount_debit'] ?? 0,
                        'amount_credit' => $detail['amount_credit'] ?? 0,
                    ]);
                }
            }

            // Recalcular y guardar el balance del asiento
            $entry->is_balanced = $entry->calculateBalance();
            $entry->save();

            DB::commit();
            return $entry;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Carga las relaciones de un asiento.
     *
     * @param Entry $entry
     * @return Entry
     */
    public function loadEntryRelations(Entry $entry): Entry
    {
        return $entry->load(['entryType', 'currency', 'details']);
    }

    /**
     * Elimina un asiento específico.
     *
     * @param int $entryId
     * @return void
     */
    public function destroyEntry(int $entryId): void
    {
        $entry = Entry::findOrFail($entryId);
        $entry->details()->delete();
        $entry->delete();
    }

    /**
     * Elimina varios asientos.
     *
     * @param array $entryIds
     * @return void
     */
    public function deleteMultipleEntries(array $entryIds): void
    {
        DB::beginTransaction();

        try {
            // Primero, eliminar los detalles de los asientos que se van a eliminar
            EntryDetail::whereIn('entry_id', $entryIds)->delete();

            // Luego, eliminar los asientos
            Entry::whereIn('id', $entryIds)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * Obtiene los asientos para la DataTable.
     *
     * @return mixed
     */
    public function getEntriesForDataTable(Request $request): mixed
    {
        $query = Entry::select([
            'entries.id',
            'entries.entry_date',
            'entries.is_balanced',
            'entries.concept',
            'entries.created_at',
            'entry_types.name as entry_type_name',
            'currencies.name as currency_name',
        ])
            ->join('entry_types', 'entries.entry_type_id', '=', 'entry_types.id')
            ->join('currencies', 'entries.currency_id', '=', 'currencies.id')
            ->orderBy('entries.id', 'desc');

        $dataTable = DataTables::of($query)
            ->addColumn('total_credit', function ($entry) {
                return $entry->total_credit;
            })
            ->addColumn('total_debit', function ($entry) {
                return $entry->total_debit;
            })
            ->make(true);
        return $dataTable;
    }

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
     * Obtiene todas las monedas.
     *
     * @return mixed
     */
    public function getAllCurrencies(): mixed
    {
        return Currency::all();
    }

    /**
     * Obtiene todo las cuentas contables.
     *
     * @return mixed
     */
    public function getAllAccounts(): mixed
    {
        return EntryAccount::all();
    }

    public function getEntriesForExport($entryType, $currencyId, $startDate, $endDate)
    {
        $entryType = EntryType::where('name', $entryType)->first();
        $currencyId = Currency::where('name', $currencyId)->first();
        $query = Entry::with(['entryType', 'currency', 'details'])
            ->when($entryType, function ($q) use ($entryType) {
                return $q->where('entry_type_id', $entryType->id);
            })
            ->when($currencyId, function ($q) use ($currencyId) {
                return $q->where('currency_id', $currencyId->id);
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->get();
        return $query;
    }

}
