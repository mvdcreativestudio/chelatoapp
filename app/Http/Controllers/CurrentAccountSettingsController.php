<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteCurrentAccountSettingsRequest;
use App\Http\Requests\StoreCurrentAccountSettingsRequest;
use App\Http\Requests\UpdateCurrentAccountSettingsRequest;
use App\Repositories\CurrentAccountSettingsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CurrentAccountSettingsController extends Controller
{
    /**
     * El repositorio para las operaciones de configuraciones de cuentas corrientes.
     *
     * @var CurrentAccountSettingsRepository
     */
    protected $currentAccountSettingsRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param CurrentAccountSettingsRepository $currentAccountSettingsRepository
     */
    public function __construct(CurrentAccountSettingsRepository $currentAccountSettingsRepository)
    {
        $this->middleware(['check_permission:access_current-account-settings'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_current-account-settings'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->currentAccountSettingsRepository = $currentAccountSettingsRepository;
    }

    /**
     * Muestra una lista de todas las configuraciones de cuentas corrientes.
     *
     * @return View
     */
    public function index(): View
    {
        $currentAccountSettings = $this->currentAccountSettingsRepository->getAllCurrentAccountSettings();
        return view('current-accounts.current-account-settings.index', $currentAccountSettings);
    }

    /**
     * Muestra el formulario para crear una nueva configuración de cuenta corriente.
     *
     * @return View
     */
    public function create(): View
    {
        return view('current_account.settings.create');
    }

    /**
     * Almacena una nueva configuración de cuenta corriente en la base de datos.
     *
     * @param StoreCurrentAccountSettingsRequest $request
     * @return JsonResponse
     */
    public function store(StoreCurrentAccountSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $currentAccountSettings = $this->currentAccountSettingsRepository->store($validated);
            return response()->json(['success' => true, 'data' => $currentAccountSettings]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar la configuración de cuenta corriente.'], 400);
        }
    }

    /**
     * Muestra una configuración de cuenta corriente específica.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $currentAccountSettings = $this->currentAccountSettingsRepository->getCurrentAccountSettingsById($id);
        return view('current_account.settings.details', compact('currentAccountSettings'));
    }

    /**
     * Devuelve los datos de una configuración de cuenta corriente específica para edición.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $currentAccountSettings = $this->currentAccountSettingsRepository->getCurrentAccountSettingsById($id);
            return response()->json($currentAccountSettings);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos de la configuración de cuenta corriente.'], 400);
        }
    }

    /**
     * Actualiza una configuración de cuenta corriente específica.
     *
     * @param UpdateCurrentAccountSettingsRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCurrentAccountSettingsRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $currentAccountSettings = $this->currentAccountSettingsRepository->update($id, $validated);
            return response()->json(['success' => true, 'data' => $currentAccountSettings]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar la configuración de cuenta corriente.'], 400);
        }
    }

    /**
     * Elimina una configuración de cuenta corriente específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(DeleteCurrentAccountSettingsRequest $request, int $id): JsonResponse
    {
        try {
            $this->currentAccountSettingsRepository->destroyCurrentAccountSettings($id);
            return response()->json(['success' => true, 'message' => 'Configuración de cuenta corriente eliminada correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la configuración de cuenta corriente.'], 400);
        }
    }

    /**
     * Elimina varias configuraciones de cuenta corriente.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(DeleteCurrentAccountSettingsRequest $request): JsonResponse
    {
        try {
            $this->currentAccountSettingsRepository->deleteMultipleCurrentAccountSettings($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Configuraciones de cuentas corrientes eliminadas correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar las configuraciones de cuentas corrientes.'], 400);
        }
    }

    /**
     * Obtiene las configuraciones de cuentas corrientes para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->currentAccountSettingsRepository->getCurrentAccountSettingsForDataTable($request);
    }
}
