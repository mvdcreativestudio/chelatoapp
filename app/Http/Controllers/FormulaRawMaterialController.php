<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRawMaterialRequest;
use App\Http\Requests\UpdateRawMaterialRequest;
use App\Repositories\FormulaRawMaterialRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;



class FormulaRawMaterialController extends Controller
{
    protected $formulaRawMaterialRepository;

    public function __construct(FormulaRawMaterialRepository $formulaRawMaterialRepository)
    {
        $this->formulaRawMaterialRepository = $formulaRawMaterialRepository;
    }

    public function storeFormulaId(Request $request)
    {
        $id = $request->input('id');
        session(['formula_id' => $id]);
        return response()->json(['success' => true]);
    }

    public function index()
    {
        $id = session('formula_id');
        $formulaRawMaterials = $this->formulaRawMaterialRepository->getAll($id);
        Log::info($formulaRawMaterials);
        return view('formula-steps.index', compact('formulaRawMaterials', 'id'));
    }

    public function create()
    {
        return view('formula_raw_materials.create');
    }


    public function getFormulaStepsById(Request $request)
    {
        $formulaId = $request->input('formula_id');

        try {
            $formulaSteps = $this->formulaRawMaterialRepository->getAll($formulaId);

            return response()->json($formulaSteps);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }


    public function store(StoreRawMaterialRequest $request)
    {
        $formulaRawMaterial = $this->formulaRawMaterialRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Material crudo para la fórmula creado con éxito.',
                'formulaRawMaterial' => $formulaRawMaterial
            ]);
        }

        return redirect()->route('formula-steps.index')->with('success', 'Material crudo para la fórmula creado con éxito.');
    }

    public function show($id)
    {
        $formulaRawMaterial = $this->formulaRawMaterialRepository->find($id);
        return view('formula_raw_materials.show', compact('formulaRawMaterial'));
    }

    public function edit($id)
    {
        $formulaRawMaterial = $this->formulaRawMaterialRepository->find($id);
        return view('formula_raw_materials.edit', compact('formulaRawMaterial'));
    }

    public function update(UpdateRawMaterialRequest $request, $id)
    {
        $this->formulaRawMaterialRepository->update($id, $request->validated());
        return redirect()->route('formula-raw-materials.index')->with('success', 'Material crudo para la fórmula actualizado con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->formulaRawMaterialRepository->delete($id);
        if ($deleted) {
            return response()->json(['success' => 'Paso eliminado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Hubo un error al intentar eliminar el paso.'], 500);
        }
    }

    public function storeCSV(Request $request)
    {
        $request->validate([
            'csvFile' => 'required|mimes:csv,txt|max:2048',
        ]);

        if ($request->hasFile('csvFile')) {
            $file = $request->file('csvFile');
            $fileHandle = fopen($file->getPathname(), 'r');
            stream_filter_append($fileHandle, 'convert.iconv.ISO-8859-1.UTF-8');

            // Leer y registrar los encabezados para depuración
            $header = fgetcsv($fileHandle);
            Log::info('CSV Headers:', $header);

            // Mapeo de columnas español -> inglés
            $columnMap = [
                'ID Materia prima' => 'raw_material_id',
                'Cantidad requerida' => 'quantity_required',
                'Aclaracion' => 'clarification',
                'Paso' => 'step'
            ];

            $rows = [];
            $errors = [];
            $index = 0;

            while (($row = fgetcsv($fileHandle)) !== false) {
                $mappedRow = [];
                foreach ($header as $key => $spanishHeader) {
                    $englishHeader = $columnMap[$spanishHeader] ?? null;
                    if ($englishHeader) {
                        $mappedRow[$englishHeader] = trim(mb_convert_encoding($row[$key], 'UTF-8', 'auto'));
                    }
                }

                // Asignar valores por defecto si es necesario
                $mappedRow['raw_material_id'] = $mappedRow['raw_material_id'] ?? null;
                $mappedRow['quantity_required'] = $mappedRow['quantity_required'] ?? 0;
                $mappedRow['clarification'] = $mappedRow['clarification'] ?? '';
                // 'step' es obligatorio, no se asigna por defecto

                // Registrar la fila mapeada para depuración
                Log::info("Mapped Row [Index $index]:", $mappedRow);

                // Validación usando $mappedRow
                $validator = Validator::make($mappedRow, [
                    'raw_material_id' => 'nullable|exists:raw_materials,id',
                    'quantity_required' => 'nullable|numeric|min:0',
                    'step' => 'required|integer|min:1',
                    'clarification' => 'nullable|string|max:255',
                ]);

                if ($validator->fails()) {
                    $errors[$index] = $validator->errors()->all();
                    Log::warning("Validation Failed [Index $index]:", $validator->errors()->all());
                } else {
                    $rows[] = $mappedRow; // Almacenar $mappedRow en lugar de $row
                }

                $index++;
            }
            fclose($fileHandle);

            if (!empty($errors)) {
                return response()->json(['success' => false, 'errors' => $errors], 400);
            }

            // Obtener el formula_id de la sesión
            $formulaId = session('formula_id');
            if (!$formulaId) {
                return response()->json(['error' => 'No se encontró el formula_id en la sesión'], 400);
            }

            $this->formulaRawMaterialRepository->bulkInsert($rows, $formulaId);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'errors' => ['No file uploaded']], 400);
    }

    public function storeMultiple(Request $request)
    {
        // Validación de los datos
        $validatedData = $request->validate([
            'steps.*.raw-material' => 'nullable|exists:raw_materials,id',
            'steps.*.quantity' => 'nullable|numeric',
            'steps.*.clarification' => 'nullable|string',
            'steps.*.step' => 'required|integer',
        ]);

        // Obtener el formula_id de la sesión
        $formulaId = session('formula_id');
        if (!$formulaId) {
            return response()->json(['error' => 'No se encontró el formula_id en la sesión'], 400);
        }

        $steps = [];
        foreach ($validatedData['steps'] as $step) {
            // Formatear los datos para que coincidan con lo que espera el método del repositorio
            $steps[] = [
                'raw_material_id' => $step['raw-material'] ?? null,
                'quantity_required' => isset($step['quantity']) && $step['quantity'] !== ''
                    ? str_replace(',', '.', $step['quantity'])  // Convertir coma a punto
                    : 0.00, // Si no se ingresa cantidad, establecer en 0.00                
                'step' => $step['step'],
                'clarification' => $step['clarification'] ?? null,
            ];
        }
        Log::info($steps);

        $this->formulaRawMaterialRepository->bulkInsert($steps, $formulaId);

        return response()->json(['success' => true, 'message' => 'Pasos cargados correctamente.']);
    }
}
