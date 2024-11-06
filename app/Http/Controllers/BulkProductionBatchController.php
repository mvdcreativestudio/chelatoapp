<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBulkProductionBatchRequest;
use App\Http\Requests\UpdateBulkProductionBatchRequest;
use App\Repositories\BulkProductionBatchRepository;
use Illuminate\Http\Request;

class BulkProductionBatchController extends Controller
{
    protected $bulkProductionBatchRepository;

    public function __construct(BulkProductionBatchRepository $bulkProductionBatchRepository)
    {
        $this->bulkProductionBatchRepository = $bulkProductionBatchRepository;
    }

    public function index()
    {
        $bulkProductionBatches = $this->bulkProductionBatchRepository->getAll();
        return view('bulk-production-batches.index', compact('bulkProductionBatches'));
    }

    public function create()
    {
        return view('bulk-production-batches.create');
    }

    public function store(StoreBulkProductionBatchRequest $request)
    {
        $bulkProductionBatch = $this->bulkProductionBatchRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lote de producción masiva creado con éxito.',
                'bulkProductionBatch' => $bulkProductionBatch
            ]);
        }

        return redirect()->route('bulk-production-batches.index')->with('success', 'Lote de producción masiva creado con éxito.');
    }

    public function show($id)
    {
        $bulkProductionBatch = $this->bulkProductionBatchRepository->find($id);
        return view('bulk-production-batches.show', compact('bulkProductionBatch'));
    }

    public function edit($id)
    {
        $bulkProductionBatch = $this->bulkProductionBatchRepository->find($id);
        return view('bulk-production-batches.edit', compact('bulkProductionBatch'));
    }

    public function update(UpdateBulkProductionBatchRequest $request, $id)
    {
        $this->bulkProductionBatchRepository->update($id, $request->validated());
        return redirect()->route('bulk-production-batches.index')->with('success', 'Lote de producción masiva actualizado con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->bulkProductionBatchRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Lote de producción masiva borrado exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el lote que se deseó borrar.'], 404);
        }
    }
}
