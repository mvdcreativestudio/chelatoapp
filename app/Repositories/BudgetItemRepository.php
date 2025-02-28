<?php

namespace App\Repositories;

use App\Models\BudgetItem;

class BudgetItemRepository
{
    /**
     * Obtiene todos los items de presupuesto.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return BudgetItem::all();
    }

    /**
     * Obtiene un item de presupuesto por su ID.
     *
     * @param int $id
     * @return BudgetItem
     */
    public function findById($id)
    {
        return BudgetItem::findOrFail($id);
    }

    /**
     * Crea un nuevo item de presupuesto.
     *
     * @param array $data
     * @return BudgetItem
     */
    public function create(array $data)
    {
        return BudgetItem::create($data);
    }

    /**
     * Actualiza un item de presupuesto existente.
     *
     * @param BudgetItem $budgetItem
     * @param array $data
     * @return BudgetItem
     */
    public function update(BudgetItem $budgetItem, array $data)
    {
        $budgetItem->update($data);
        return $budgetItem;
    }

    /**
     * Elimina un item de presupuesto.
     *
     * @param BudgetItem $budgetItem
     * @return void
     */
    public function delete(BudgetItem $budgetItem)
    {
        $budgetItem->delete();
    }
}