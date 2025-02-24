<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetStatusRequest;
use App\Http\Requests\UpdateBudgetStatusRequest;
use App\Repositories\BudgetStatusRepository;
use App\Models\BudgetStatus;
use Illuminate\Http\Request;

class BudgetStatusController extends Controller
{
    protected $budgetStatusRepository;

    public function __construct(BudgetStatusRepository $budgetStatusRepository)
    {
        $this->budgetStatusRepository = $budgetStatusRepository;
    }

    public function index()
    {
        $budgetStatuses = $this->budgetStatusRepository->getAll();
        return view('budget_statuses.index', compact('budgetStatuses'));
    }

    public function show($id)
    {
        $budgetStatus = $this->budgetStatusRepository->findById($id);
        return view('budget_statuses.show', compact('budgetStatus'));
    }

    public function create()
    {
        return view('budget_statuses.create');
    }

    public function store(StoreBudgetStatusRequest $request)
    {
        $budgetStatus = $this->budgetStatusRepository->create($request->validated());
        return redirect()->route('budget_statuses.show', $budgetStatus->id);
    }

    public function edit($id)
    {
        $budgetStatus = $this->budgetStatusRepository->findById($id);
        return view('budget_statuses.edit', compact('budgetStatus'));
    }

    public function update(UpdateBudgetStatusRequest $request, $id)
    {
        $budgetStatus = $this->budgetStatusRepository->findById($id);
        $this->budgetStatusRepository->update($budgetStatus, $request->validated());
        return redirect()->route('budget_statuses.show', $budgetStatus->id);
    }

    public function destroy($id)
    {
        $budgetStatus = $this->budgetStatusRepository->findById($id);
        $this->budgetStatusRepository->delete($budgetStatus);
        return redirect()->route('budget_statuses.index');
    }
}