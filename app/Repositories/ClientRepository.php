<?php

namespace App\Repositories;

use App\Models\Client;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;


class ClientRepository
{

    protected $companySettings;

    public function __construct($companySettings)
    {
        // Asigna companySettings al repositorio
        $this->companySettings = $companySettings;
    }

    /**
     * Obtiene todos los clientes para la tabla de datos según la configuración de clients_has_store.
     *
     * @return mixed
     */
    public function getClientsForDatatable(): mixed
    {
        // Iniciar la consulta básica
        $query = Client::select(['id', 'name', 'lastname', 'company_name', 'type', 'rut', 'ci', 'address', 'city', 'state', 'country', 'phone', 'email', 'website', 'logo', 'doc_type', 'document'])
            ->orderBy('name', 'asc');

        // Verificar la configuración de clients_has_store
        if ($this->companySettings && $this->companySettings->clients_has_store == 1) {
            // Filtrar clientes por el store_id del usuario autenticado
            $query->where('store_id', Auth::user()->store_id);
        }

        // Retornar los resultados formateados para DataTables
        return DataTables::of($query)->make(true);
    }


    /**
     * Crea un nuevo cliente.
     *
     * @param array $data
     * @return Client
     */
    public function createClient(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Obtiene un cliente por su ID.
     *
     * @param int $id
     * @return Client|null
     */
    public function getClientById(int $id): ?Client
    {
        return Client::with('orders')->find($id);
    }

    /**
     * Actualiza un cliente existente.
     *
     * @param int $id
     * @param array $data
     * @return Client
     */
    public function updateClient(int $id, array $data): Client
    {
        $client = Client::findOrFail($id);
        $client->update($data);
        return $client;
    }

    /**
     * Elimina un cliente por su ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteClient(int $id): bool
    {
        $client = Client::find($id);
        if ($client) {
            return $client->delete();
        }
        return false;
    }
}
