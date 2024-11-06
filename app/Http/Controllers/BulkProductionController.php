<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBulkProductionRequest;
use App\Http\Requests\UpdateBulkProductionRequest;
use App\Repositories\BulkProductionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class BulkProductionController extends Controller
{
    protected $bulkProductionRepository;

    public function __construct(BulkProductionRepository $bulkProductionRepository)
    {
        $this->bulkProductionRepository = $bulkProductionRepository;
    }

    public function getAll()
    {
        $bulkProductions = $this->bulkProductionRepository->getAll();
        return response()->json([$bulkProductions]);
    }


    public function index()
    {
        $bulkProductions = $this->bulkProductionRepository->getAll();
        return view('bulk-productions.index', compact('bulkProductions'));
    }

    public function create()
    {
        return view('bulk_productions.create');
    }

    public function startProduction(Request $request)
    {
        $request->validate([
            'formula_id' => 'required|integer|exists:formulas,id',
            'quantity' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->bulkProductionRepository->startProduction(
                $request->input('batch_number'),
                $request->input('formula_id'),
                $request->input('quantity')
            );

            Log::info($result);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }



    public function store(StoreBulkProductionRequest $request)
    {
        $bulkProduction = $this->bulkProductionRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Producción masiva creada con éxito.',
                'bulkProduction' => $bulkProduction
            ]);
        }

        return redirect()->route('bulk_productions.index')->with('success', 'Producción masiva creada con éxito.');
    }

    public function show($id)
    {
        $bulkProduction = $this->bulkProductionRepository->find($id);
        return view('bulk_productions.show', compact('bulkProduction'));
    }

    public function edit($id)
    {
        $bulkProduction = $this->bulkProductionRepository->find($id);
        return view('bulk_productions.edit', compact('bulkProduction'));
    }

    public function update(UpdateBulkProductionRequest $request, $id)
    {
        $this->bulkProductionRepository->update($id, $request->validated());
        return redirect()->route('bulk_productions.index')->with('success', 'Producción masiva actualizada con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->bulkProductionRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Producción masiva borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar la producción masiva que se deseó borrar.'], 404);
        }
    }

    public function getBatches($id)
    {
        return $this->bulkProductionRepository->getById($id);
    }


    public function showBatchInfo($identifier)
    {
        $batches = $this->bulkProductionRepository->getBatchInfoByIdentifier($identifier);
        return view('bulk-productions.show', compact('batches'));
    }
}
