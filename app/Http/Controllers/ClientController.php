<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Repositories\ClientRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\CompanySettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * El repositorio de clientes.
     *
     * @var ClientRepository
     */
    protected ClientRepository $clientRepository;

    /**
     * Constructor para inyectar el repositorio.
     *
     * @param ClientRepository $clientRepository
     */
    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * Muestra la página de lista de clientes.
     *
     * @return View
     */
    public function index(): View
    {
      $companySettings = CompanySettings::first();
      $store = Auth::user()->store_id;
      return view('content.clients.clients', compact('companySettings', 'store'));
    }

    /**
     * Almacena un nuevo cliente en la base de datos.
     *
     * @param StoreClientRequest $request
     * @return RedirectResponse
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $companySettings = CompanySettings::first();
            if ($companySettings->clients_has_store == 1) {
                $validatedData['store_id'] = Auth::user()->store_id;
            }

            $this->clientRepository->createClient($validatedData);
            return redirect()->route('clients.index')->with('success', 'Cliente creado correctamente.');
        } catch (\Throwable $th) {
            return redirect()->route('clients.index')->with('error', 'Error al crear el cliente.');
        }
    }

    /**
     * Muestra los detalles de un cliente específico.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $client = $this->clientRepository->getClientById($id);
        return view('content.clients.show', compact('client'));
    }

    /**
     * Muestra el formulario para editar un cliente existente.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $client = $this->clientRepository->getClientById($id);

        $orders = $client->orders;

        return view('content.clients.edit', compact('client'));
    }

    /**
     * Actualiza un cliente existente en la base de datos.
     *
     * @param UpdateClientRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
      \Log::info('Datos recibidos en la actualización:', $request->all());

        try {
            $validatedData = $request->validated();
            $client = $this->clientRepository->updateClient($id, $validatedData);
            return response()->json(['success' => true, 'message' => 'Cliente actualizado correctamente.', 'client' => $client]);
        } catch (\Exception $e) {
            // Captura el error completo en los logs para obtener más detalles
            \Log::error('Error al actualizar cliente: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar el cliente: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Elimina un cliente específico de la base de datos.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->clientRepository->deleteClient($id);
        return redirect()->route('clients.index');
    }

    /**
     * Obtiene los datos para mostrar en la tabla de clientes.
     *
     * @return mixed
     */
    public function datatable(): mixed
    {
        return $this->clientRepository->getClientsForDatatable();
    }
}
