<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteEntryAccountRequest;
use App\Http\Requests\StoreEntryAccountRequest;
use App\Http\Requests\UpdateEntryAccountRequest;
use App\Repositories\EntryAccountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EntryAccountController extends Controller
{
    /**
     * El repositorio para las operaciones de cuentas contables.
     *
     * @var EntryAccountRepository
     */
    protected $entryAccountRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param EntryAccountRepository $entryAccountRepository
     */
    public function __construct(EntryAccountRepository $entryAccountRepository)
    {
        $this->middleware(['check_permission:access_entry-accounts', 'user_has_store'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_entry-accounts'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->entryAccountRepository = $entryAccountRepository;
    }

    /**
     * Muestra una lista de todas las cuentas contables.
     *
     * @return View
     */
    public function index(): View
    {
        $entryAccounts = $this->entryAccountRepository->getAllEntryAccounts();
        return view('content.accounting.entries.entry-accounts.index', compact('entryAccounts'));
    }

    /**
     * Muestra el formulario para crear una nueva cuenta contable.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.entry_accounts.create-entry-account');
    }

    /**
     * Almacena una nueva cuenta contable en la base de datos.
     *
     * @param StoreEntryAccountRequest $request
     * @return JsonResponse
     */
    public function store(StoreEntryAccountRequest $request): JsonResponse
    {
        $validated = $request->validated(); // Utiliza el FormRequest para validar

        try {
            $entryAccount = $this->entryAccountRepository->store($validated);
            return response()->json(['success' => true, 'data' => $entryAccount]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar la cuenta contable.'], 400);
        }
    }

    /**
     * Muestra una cuenta contable específica.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $entryAccount = $this->entryAccountRepository->getEntryAccountById($id);
        return view('content.accounting.entry_accounts.details-entry-account', compact('entryAccount'));
    }

    /**
     * Devuelve los datos de una cuenta contable específica para edición.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $entryAccount = $this->entryAccountRepository->getEntryAccountById($id);
            return response()->json($entryAccount);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos de la cuenta contable.'], 400);
        }
    }

    /**
     * Actualiza una cuenta contable específica.
     *
     * @param UpdateEntryAccountRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateEntryAccountRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated(); // Utiliza el FormRequest para validar

        try {
            $entryAccount = $this->entryAccountRepository->update($id, $validated);
            return response()->json(['success' => true, 'data' => $entryAccount]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar la cuenta contable.'], 400);
        }
    }

    /**
     * Elimina una cuenta contable específica.
     *
     * @param DeleteEntryAccountRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(DeleteEntryAccountRequest $request, int $id): JsonResponse
    {
        try {
            // El FormRequest ya valida si la cuenta contable tiene detalles de asiento asociados
            $this->entryAccountRepository->destroyEntryAccount($id);
            return response()->json(['success' => true, 'message' => 'Cuenta contable eliminada correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la cuenta contable.'], 400);
        }
    }

    /**
     * Elimina varias cuentas contables.
     *
     * @param DeleteEntryAccountRequest $request
     * @return JsonResponse
     */
    public function deleteMultiple(DeleteEntryAccountRequest $request): JsonResponse
    {
        try {
            // El FormRequest valida que las cuentas contables a eliminar no tengan detalles asociados
            $this->entryAccountRepository->deleteMultipleEntryAccounts($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Cuentas contables eliminadas correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar las cuentas contables.'], 400);
        }
    }

    /**
     * Obtiene las cuentas contables para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->entryAccountRepository->getEntryAccountsForDataTable($request);
    }
}
