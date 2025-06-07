<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventStoreConfigurationRequest;
use App\Repositories\EventStoreConfigurationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EventStoreConfigurationController extends Controller
{
    protected $eventStoreConfigurationRepository;

    public function __construct(EventStoreConfigurationRepository $eventStoreConfigurationRepository)
    {
        $this->middleware(['check_permission:access_event-configurations', 'user_has_store'])->only(
            [
                'show',
                // 'create',
                // 'store',
                // 'edit',
                // 'update',
                // 'destroy',
            ]
        );

        $this->eventStoreConfigurationRepository = $eventStoreConfigurationRepository;
    }

    /**
     * Muestra las configuraciones de eventos de una tienda específica.
     *
     * @param int $storeId
     * @return View
     */
    public function show(int $storeId): View
    {
        $eventConfigurations = $this->eventStoreConfigurationRepository->getConfigurationsByStore($storeId);
        return view('stores.event-notifications.index', $eventConfigurations);
    }


    /**
     * Activa o desactiva la configuración de un evento para una tienda específica.
     *
     * @param Request $request
     * @param int $storeId
     * @param int $eventId
     * @return JsonResponse
     */
    public function toggleEvent(Request $request): JsonResponse
    {
        try {
            $storeId = $request->input('store_id');
            $eventId = $request->input('event_id');
            $isActive = $request->input('is_active');
            $result = $this->eventStoreConfigurationRepository->toggleEvent($storeId, $eventId, $isActive);

            return response()->json(['success' => true, 'message' => 'Estado del evento actualizado correctamente.', 'data' => $result]);
        } catch (\Exception $e) {
            dd($e);
            Log::error('Error al actualizar el estado del evento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar el estado del evento.'], 500);
        }
    }


    /**
     * Muestra el formulario para crear una nueva configuración de evento.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.e-commerce.backoffice.event-configurations.create');
    }

    /**
     * Almacena una nueva configuración de evento en la base de datos.
     *
     * @param StoreEventStoreConfigurationRequest $request
     * @return RedirectResponse
     */
    // public function store(StoreEventStoreConfigurationRequest $request): RedirectResponse
    // {
    //     try {
    //         $this->eventStoreConfigurationRepository->store($request->validated());

    //         return redirect()->route('event-configurations.index')->with('success', 'Configuración de evento creada con éxito.');
    //     } catch (\Exception $e) {
    //         Log::error('Error al crear la configuración de evento: ' . $e->getMessage());
    //         return back()->withErrors('Error al crear la configuración de evento.')->withInput();
    //     }
    // }

    /**
     * Muestra el formulario para editar una configuración de evento específica.
     *
     * @param int $id
     * @return View
     */
    // public function edit(int $id): View
    // {
    //     $eventConfiguration = EventStoreConfiguration::findOrFail($id);

    //     return view('content.e-commerce.backoffice.event-configurations.edit', compact('eventConfiguration'));
    // }

    /**
     * Actualiza una configuración de evento específica.
     *
     * @param StoreEventStoreConfigurationRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    // public function update(StoreEventStoreConfigurationRequest $request, int $id): RedirectResponse
    // {
    //     try {
    //         $this->eventStoreConfigurationRepository->update($id, $request->validated());

    //         return redirect()->route('event-configurations.index')->with('success', 'Configuración de evento actualizada con éxito.');
    //     } catch (\Exception $e) {
    //         Log::error('Error al actualizar la configuración de evento: ' . $e->getMessage());
    //         return back()->withErrors('Error al actualizar la configuración de evento.')->withInput();
    //     }
    // }

    /**
     * Elimina una configuración de evento específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    // public function destroy(int $id): JsonResponse
    // {
    //     try {
    //         $this->eventStoreConfigurationRepository->destroy($id);

    //         return response()->json(['success' => true, 'message' => 'Configuración de evento eliminada correctamente.']);
    //     } catch (\Exception $e) {
    //         Log::error('Error al eliminar la configuración de evento: ' . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Error al eliminar la configuración de evento.'], 400);
    //     }
    // }
}
