<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormulaRequest;
use App\Http\Requests\UpdateFormulaRequest;
use App\Repositories\FormulaRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;use Illuminate\Support\Facades\Log;

class FormulaController extends Controller
{
    protected $formulaRepository;
    protected $productRepository;

    public function __construct(FormulaRepository $formulaRepository, ProductRepository $productRepository )
    {
        $this->formulaRepository = $formulaRepository;
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        $formulas = $this->formulaRepository->getAll();
        Log::info($formulas);
        $products = $this->productRepository->getAll();
        return view('formulas.index', compact('formulas','products'));
    }

    public function getAll()
    {
        $formulas = $this->formulaRepository->getAll();
        return response()->json(['formulas' => $formulas]);
    }


    public function create()
    {
        return view('formulas.create');
    }

    public function store(StoreFormulaRequest $request)
    {
        $formula = $this->formulaRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Fórmula creada con éxito.',
                'formula' => $formula
            ]);
        }

        return redirect()->route('formulas.index')->with('success', 'Fórmula creada con éxito.');
    }

    public function show($id)
    {
        $formula = $this->formulaRepository->find($id);
        return view('formulas.show', compact('formula'));
    }

    public function edit($id)
    {
        $formula = $this->formulaRepository->find($id);
        return view('formulas.edit', compact('formula'));
    }

    public function update(UpdateFormulaRequest $request, $id)
    {
        $this->formulaRepository->update($id, $request->validated());
        return redirect()->route('formulas.index')->with('success', 'Fórmula actualizada con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->formulaRepository->delete($id);
        if ($deleted) {
            return response()->json(['success' => 'Fórmula eliminada correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar la fórmula.'], 500);
        }
    }
}
