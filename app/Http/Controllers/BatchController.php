<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Repositories\BatchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;



class BatchController extends Controller
{
    protected $batchRepository;

    public function __construct(BatchRepository $batchRepository)
    {
        $this->batchRepository = $batchRepository;
    }

    public function index()
    {
        $batches = $this->batchRepository->getAll();
        $totalBatches = $batches->count();
        $batches = $batches->map(function ($batch) {
            $batch->production_date = Carbon::parse($batch->production_date)->format('d-m-Y');
            $batch->expiration_date = Carbon::parse($batch->expiration_date)->format('d-m-Y');
            return $batch;
        });
        return view('batches.index', compact('batches', 'totalBatches'));
    }

    public function create()
    {
        return view('batches.create');
    }

    public function store(StoreBatchRequest $request)
    {
        $batch = $this->batchRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lote creado con éxito.',
                'batch' => $batch
            ]);
        }

        return redirect()->route('batches.index')->with('success', 'Lote creado con éxito.');
    }

    public function show($id)
    {
        $batch = $this->batchRepository->find($id);
        return view('batches.show', compact('batch'));
    }

    public function edit($id)
    {
        $batch = $this->batchRepository->find($id);
        return view('batches.edit', compact('batch'));
    }

    public function update(UpdateBatchRequest $request, $id)
    {
        $this->batchRepository->update($id, $request->validated());
        return redirect()->route('batches.index')->with('success', 'Lote actualizado con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->batchRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Lote borrado exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el lote que se deseó borrar.'], 404);
        }
    }

    public function storeBatches(Request $request)
    {
        $batches = $request->input('batches');
        Log::info($request);

        $purchaseEntriesId = $request->input('purchase_entries_id');
        $errors = [];
    
        // Validación de purchase_entries_id
        if (empty($purchaseEntriesId)) {
            return response()->json(['error' => 'El ID de entrada de compra es requerido.'], 422);
        }
    
        // Validar cada lote individualmente
        foreach ($batches as $batch) {
            $batchRequest = new StoreBatchRequest();
            $batchRequest->merge($batch); // Mezcla los datos del lote actual
            $validator = Validator::make($batchRequest->all(), $batchRequest->rules());
    
            if ($validator->fails()) {
                $errors[] = [
                    'batch_number' => $batch['batch_number'],
                    'validation_errors' => $validator->errors(),
                ];
            }
        }
    
        if (!empty($errors)) {
            return response()->json([
                'error' => 'Validación fallida para algunos lotes.',
                'details' => $errors
            ], 422);
        }
    
        // Si todas las validaciones de datos pasan, guarda los lotes
        $data = [
            'batches' => $batches,
            'purchase_entries_id' => $purchaseEntriesId,
        ];
    
        // Llamar al método del repositorio para guardar los datos
        $result = $this->batchRepository->createBatches($data);
    
        // Manejo de errores o advertencias devueltas por el repositorio
        if (is_array($result) && !empty($result)) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Algunos lotes no pudieron ser guardados debido a la falta de Producto o Materia Prima',
                'errors' => $result
            ], 200);
        }
    
        if ($result === true) {
            return response()->json(['status' => 'success', 'message' => 'Lotes guardados correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Hubo un error al guardar los lotes.'], 500);
        }
    }
    
}
