<?php

namespace App\Repositories;

use App\Models\BudgetStatus;

class BudgetStatusRepository
{
    /**
     * Obtiene todos los estados de presupuesto.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return BudgetStatus::all();
    }

    /**
     * Obtiene un estado de presupuesto por su ID.
     *
     * @param int $id
     * @return BudgetStatus
     */
    public function findById($id)
    {
        return BudgetStatus::findOrFail($id);
    }

    /**
     * Crea un nuevo estado de presupuesto.
     *
     * @param array $data
     * @return BudgetStatus
     */
    public function create(array $data)
    {
        return BudgetStatus::create($data);
    }

    /**
     * Actualiza un estado de presupuesto existente.
     *
     * @param BudgetStatus $budgetStatus
     * @param array $data
     * @return BudgetStatus
     */
    public function update(BudgetStatus $budgetStatus, array $data)
    {
        $budgetStatus->update($data);
        return $budgetStatus;
    }

    /**
     * Elimina un estado de presupuesto.
     *
     * @param BudgetStatus $budgetStatus
     * @return void
     */
    public function delete(BudgetStatus $budgetStatus)
    {
        $budgetStatus->delete();
    }
}