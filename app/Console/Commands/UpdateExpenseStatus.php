<?php

namespace App\Console\Commands;

use App\Enums\Expense\ExpenseStatusEnum;
use App\Models\Expense;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateExpenseStatus extends Command
{
    protected $signature = 'expenses:update-status';

    protected $description = 'Actualizar el estado de las expenses diariamente';

    public function handle()
    {
        // Inicia una transacción
        DB::beginTransaction();

        try {
            // Obtén todas las expenses
            $expenses = Expense::all();

            foreach ($expenses as $expense) {
                $expense->temporal_status = $expense->calculateTemporalStatus();
                $expense->save();
            }

            // Confirma la transacción
            DB::commit();
            $this->info('El estado de las expenses ha sido actualizado correctamente.');

        } catch (\Exception $e) {
            // Revierte la transacción en caso de error
            DB::rollBack();
            $this->error('Error al actualizar el estado de las expenses: ' . $e->getMessage());
        }
    }
}
