<?php

namespace App\Repositories;

use App\Enums\CurrentAccounts\TransactionTypeEnum;
use App\Models\CurrentAccountSettings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CurrentAccountSettingsRepository
{
    /**
     * Obtiene todas las configuraciones de cuentas corrientes.
     *
     * @return mixed
     */
    public function getAllCurrentAccountSettings(): mixed
    {
        $currentAccountSettings = CurrentAccountSettings::all();
        $totalCurrentAccountSettings = $currentAccountSettings->count();
        $transactionTypes = TransactionTypeEnum::getTranslateds();
        return compact('currentAccountSettings', 'totalCurrentAccountSettings', 'transactionTypes');
    }

    /**
     * Almacena una nueva configuración de cuenta corriente en la base de datos.
     *
     * @param  array  $data
     * @return CurrentAccountSettings
     */
    public function store(array $data): CurrentAccountSettings
    {
        DB::beginTransaction();

        try {
            // Crear la nueva configuración de cuenta corriente
            $currentAccountSettings = CurrentAccountSettings::create([
                'transaction_type' => $data['transaction_type'],
                'late_fee' => $data['late_fee'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
            ]);

            DB::commit();
            return $currentAccountSettings;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene una configuración de cuenta corriente específica por su ID.
     *
     * @param int $settingsId
     * @return CurrentAccountSettings
     */
    public function getCurrentAccountSettingsById(int $settingsId): CurrentAccountSettings
    {
        return CurrentAccountSettings::findOrFail($settingsId);
    }

    /**
     * Actualiza una configuración de cuenta corriente específica en la base de datos.
     *
     * @param int $settingsId
     * @param array $data
     * @return CurrentAccountSettings
     */
    public function update(int $settingsId, array $data): CurrentAccountSettings
    {
        DB::beginTransaction();

        try {
            // Buscar y actualizar la configuración de cuenta corriente
            $currentAccountSettings = CurrentAccountSettings::findOrFail($settingsId);
            $currentAccountSettings->update([
                'transaction_type' => $data['transaction_type'],
                'late_fee' => $data['late_fee'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
            ]);

            DB::commit();
            return $currentAccountSettings;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una configuración de cuenta corriente específica.
     *
     * @param int $settingsId
     * @return void
     */
    public function destroyCurrentAccountSettings(int $settingsId): void
    {
        $currentAccountSettings = CurrentAccountSettings::findOrFail($settingsId);
        $currentAccountSettings->delete();
    }

    /**
     * Elimina varias configuraciones de cuentas corrientes.
     *
     * @param array $settingsIds
     * @return void
     */
    public function deleteMultipleCurrentAccountSettings(array $settingsIds): void
    {
        DB::beginTransaction();

        try {
            // Eliminar las configuraciones de cuentas corrientes
            CurrentAccountSettings::whereIn('id', $settingsIds)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene las configuraciones de cuentas corrientes para la DataTable.
     *
     * @param Request $request
     * @return mixed
     */
    public function getCurrentAccountSettingsForDataTable(Request $request): mixed
    {
        $query = CurrentAccountSettings::select([
            'id',
            'transaction_type',
            'late_fee',
            'payment_terms',
            'created_at',
        ])->orderBy('id', 'desc');

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }
}
