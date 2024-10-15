<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteEntryTypeRequest;
use App\Http\Requests\StoreEntryTypeRequest;
use App\Http\Requests\UpdateEntryTypeRequest;
use App\Repositories\EntryTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EntryTypeController extends Controller
{
    /**
     * El repositorio para las operaciones de tipos de asientos.
     *
     * @var EntryTypeRepository
     */
    protected $entryTypeRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param EntryTypeRepository $entryTypeRepository
     */
    public function __construct(EntryTypeRepository $entryTypeRepository)
    {
        $this->middleware(['check_permission:access_entry-types', 'user_has_store'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_entry-types'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->entryTypeRepository = $entryTypeRepository;
    }

    /**
     * Muestra una lista de todos los tipos de asientos.
     *
     * @return View
     */
    public function index(): View
    {
        $entryTypes = $this->entryTypeRepository->getAllEntryTypes();
        return view('content.accounting.entries.entry-types.index', compact('entryTypes'));
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de asiento.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.entry_types.create-entry-type');
    }

    /**
     * Almacena un nuevo tipo de asiento en la base de datos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(StoreEntryTypeRequest $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $entryType = $this->entryTypeRepository->store($validated);
            return response()->json(['success' => true, 'data' => $entryType]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el tipo de asiento.'], 400);
        }
    }

    /**
     * Muestra un tipo de asiento específico.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $entryType = $this->entryTypeRepository->getEntryTypeById($id);
        return view('content.accounting.entry_types.details-entry-type', compact('entryType'));
    }

    /**
     * Devuelve los datos de un tipo de asiento específico para edición.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $entryType = $this->entryTypeRepository->getEntryTypeById($id);
            return response()->json($entryType);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos del tipo de asiento.'], 400);
        }
    }

    /**
     * Actualiza un tipo de asiento específico.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateEntryTypeRequest $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $entryType = $this->entryTypeRepository->update($id, $validated);
            return response()->json(['success' => true, 'data' => $entryType]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el tipo de asiento.'], 400);
        }
    }

    /**
     * Elimina un tipo de asiento específico.
     *
     * @param DeleteEntryTypeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(DeleteEntryTypeRequest $request, int $id): JsonResponse
    {
        try {
            // El FormRequest ya valida si el tipo de asiento tiene asientos asociados
            $this->entryTypeRepository->destroyEntryType($id);
            return response()->json(['success' => true, 'message' => 'Tipo de asiento eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el tipo de asiento.'], 400);
        }
    }

    /**
     * Elimina varios tipos de asientos.
     *
     * @param DeleteEntryTypeRequest $request
     * @return JsonResponse
     */
    public function deleteMultiple(DeleteEntryTypeRequest $request): JsonResponse
    {
        try {
            // El FormRequest valida que los tipos de asientos a eliminar no tengan asientos asociados
            $this->entryTypeRepository->deleteMultipleEntryTypes($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Tipos de asientos eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los tipos de asientos.'], 400);
        }
    }
    /**
     * Obtiene los tipos de asientos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->entryTypeRepository->getEntryTypesForDataTable($request);
    }
}
