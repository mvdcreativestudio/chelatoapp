<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Repositories\EntryRepository;
use App\Repositories\EntryDetailRepository;
use App\Models\Entry;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    /**
     * El repositorio para las operaciones de asientos.
     *
     * @var EntryRepository
     */
    protected $entryRepository;
    protected $entryDetailRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param EntryRepository $entryRepository
     * @param EntryDetailRepository $entryDetailRepository
     */
    public function __construct(EntryRepository $entryRepository, EntryDetailRepository $entryDetailRepository)
    {
        $this->middleware(['check_permission:access_entries', 'user_has_store'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
                'entryDetailsDatatable'
            ]
        );

        $this->middleware(['check_permission:access_delete_entries'])->only(
            [
                'destroy',
                'deleteMultiple'
            ]
        );

        $this->entryRepository = $entryRepository;
        $this->entryDetailRepository = $entryDetailRepository;
    }

    /**
     * Muestra una lista de todos los asientos.
     *
     * @return View
     */
    public function index(): View
    {
        $entries = $this->entryRepository->getAllEntries();
        return view('content.accounting.entries.index', $entries);
    }

    /**
     * Muestra el formulario para crear un nuevo asiento.
     *
     * @return View
     */
    public function create(): View
    {
        $entryTypes = $this->entryRepository->getAllEntryTypes();
        $currencies = $this->entryRepository->getAllCurrencies();
        $accounts = $this->entryRepository->getAllAccounts();
        return view('content.accounting.entries.add-entry', compact('entryTypes', 'currencies', 'accounts'));
    }

    /**
     * Almacena un nuevo asiento en la base de datos.
     *
     * @param StoreEntryRequest $request
     * @return JsonResponse
     */
    public function store(StoreEntryRequest $request): JsonResponse
    {
        try {
            $entry = $this->entryRepository->store($request->validated());
            return response()->json($entry);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el asiento.'], 400);
        }
    }

    /**
     * Muestra un asiento específico.
     *
     * @param Entry $entry
     * @return View
     */
    public function show(Entry $entry): View
    {
        $entry = $this->entryRepository->loadEntryRelations($entry);
        $details = $entry->details;

        return view('content.accounting.entries.details-entry', compact('entry', 'details'));
    }

    /**
     * Devuelve datos para un asiento específico.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): View
    {
        try {
            $entry = $this->entryRepository->getEntryById($id);
            $entryTypes = $this->entryRepository->getAllEntryTypes();
            $currencies = $this->entryRepository->getAllCurrencies();
            $accounts = $this->entryRepository->getAllAccounts();
            // return response()->json($entry);
            return view('content.accounting.entries.edit-entry', compact('entry', 'entryTypes', 'currencies', 'accounts'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos del asiento.'], 400);
        }
    }

    /**
     * Actualiza un asiento específico.
     *
     * @param UpdateEntryRequest $request
     * @param Entry $entry
     * @return JsonResponse
     */
    public function update(UpdateEntryRequest $request, Entry $entry): JsonResponse
    {
        try {
            $entry = $this->entryRepository->update($entry, $request->validated());
            return response()->json($entry);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el asiento.'], 400);
        }
    }

    /**
     * Eliminar un asiento específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->entryRepository->destroyEntry($id);
            return response()->json(['success' => true, 'message' => 'Asiento eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el asiento.'], 400);
        }
    }

    /**
     * Elimina varios asientos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->entryRepository->deleteMultipleEntries($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Asientos eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los asientos.'], 400);
        }
    }

    /**
     * Obtiene los asientos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->entryRepository->getEntriesForDataTable($request);
    }
}
