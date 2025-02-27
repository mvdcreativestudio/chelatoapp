<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetItemRequest;
use App\Http\Requests\UpdateBudgetItemRequest;
use App\Http\Requests\DeleteBudgetItemRequest;
use App\Repositories\BudgetItemRepository;
use App\Models\BudgetItem;
use Illuminate\Http\Request;

class BudgetItemController extends Controller
{
    protected $budgetItemRepository;

    public function __construct(BudgetItemRepository $budgetItemRepository)
    {
        $this->budgetItemRepository = $budgetItemRepository;
    }

    public function index()
    {
        $budgetItems = $this->budgetItemRepository->getAll();
        return view('budget_items.index', compact('budgetItems'));
    }

    public function show($id)
    {
        $budgetItem = $this->budgetItemRepository->findById($id);
        return view('budget_items.show', compact('budgetItem'));
    }

    public function create()
    {
        return view('budget_items.create');
    }

    public function store(StoreBudgetItemRequest $request)
    {
        $budgetItem = $this->budgetItemRepository->create($request->validated());
        return redirect()->route('budget_items.show', $budgetItem->id);
    }

    public function edit($id)
    {
        $budgetItem = $this->budgetItemRepository->findById($id);
        return view('budget_items.edit', compact('budgetItem'));
    }

    public function update(UpdateBudgetItemRequest $request, $id)
    {
        $budgetItem = $this->budgetItemRepository->findById($id);
        $this->budgetItemRepository->update($budgetItem, $request->validated());
        return redirect()->route('budget_items.show', $budgetItem->id);
    }

    public function destroy($id)
    {
        $budgetItem = $this->budgetItemRepository->findById($id);
        $this->budgetItemRepository->delete($budgetItem);
        return redirect()->route('budget_items.index');
    }
}
