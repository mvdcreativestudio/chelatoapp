<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Repositories\ClientRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\CompanySettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Models\Client;
use App\Models\PriceList;
use App\Models\Store;
use App\Models\TaxRate;
use App\Models\Vendor;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientTemplateImport;
use App\Imports\ClientImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\ClientsExport;


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
        // Cargar las configuraciones de la empresa
        $companySettings = CompanySettings::first();
        $store = Auth::user()->store_id;

        // Verificar si el usuario tiene permiso para acceder a todas las tiendas o solo a las suyas
        if (Auth::user()->can('view_all_stores') && Auth::user()->can('view_all_price-lists')) {
            $stores = Store::select('id', 'name')->get();
        } else {
            $stores = Store::select('id', 'name')->where('id', Auth::user()->store_id)->get();
        }

        // Obtener todas las listas de precios disponibles
        $priceLists = PriceList::all();

        // Obtener los vendedores disponibles
        $vendors = Vendor::select('id', 'name', 'lastname')->get();

        // Obtener Tasas de IVA disponibles (No se crean clientes con IVA Tasa Minima 10%)
        $taxRates = TaxRate::where('rate', '!=', 10.00)->get();


        return view('content.clients.clients', compact('companySettings', 'store', 'priceLists', 'stores', 'taxRates', 'vendors'));
    }


    /**
     * Almacena un nuevo cliente en la base de datos.
     *
     * @param StoreClientRequest $request
     * @return RedirectResponse
    */
    public function store(StoreClientRequest $request)
    {
        Log::info('Creando un nuevo cliente', ['request' => $request->all()]);
        try {
            // Validar los datos del cliente
            $validatedData = $request->validated();

            // Manejar la lógica de documentos
            if ($request->type === 'individual') {
                $validatedData['rut'] = null;
                $validatedData['company_name'] = null;

                // Eliminar campos no usados para clientes individuales
                if ($request->documentType === 'ci') {
                    $validatedData['passport'] = null;
                    $validatedData['other_id_type'] = null;
                } elseif ($request->documentType === 'passport') {
                    $validatedData['ci'] = null;
                    $validatedData['other_id_type'] = null;
                } elseif ($request->documentType === 'other_id_type') {
                    $validatedData['ci'] = null;
                    $validatedData['passport'] = null;
                }
            } elseif ($request->type === 'company') {
                // Eliminar campos no usados para empresas
                $validatedData['ci'] = null;
                $validatedData['passport'] = null;
                $validatedData['other_id_type'] = null;
            }

            // Asignar la tienda si está configurado
            $companySettings = CompanySettings::first();
            if ($companySettings->clients_has_store == 1) {
                $validatedData['store_id'] = Auth::user()->store_id;
            }

            // Crear el cliente
            $client = $this->clientRepository->createClient($validatedData);

            // Manejar la lista de precios asociada
            if ($request->has('price_list_id')) {
                $priceListId = $request->input('price_list_id');
                $priceListExists = DB::table('price_lists')->where('id', $priceListId)->exists();

                if ($priceListExists) {
                    DB::table('client_price_lists')->insert([
                        'client_id' => $client->id,
                        'price_list_id' => $priceListId,
                    ]);
                }
            }

            return $request->expectsJson()
                ? response()->json([
                    'success' => true,
                    'message' => 'Cliente creado correctamente.',
                    'client' => $client,
                ])
                : redirect()->route('clients.index')->with('success', 'Cliente creado correctamente.');
        } catch (\Throwable $th) {
            Log::error('Error al crear cliente: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);

            return $request->expectsJson()
                ? response()->json([
                    'success' => false,
                    'message' => 'Error al crear el cliente.',
                    'error' => $th->getMessage(),
                ], 500)
                : redirect()->route('clients.index')->with('error', 'Error al crear el cliente.');
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
        // Obtén el cliente con sus listas de precios asociadas
        $client = $this->clientRepository->getClientById($id);

        Log::error('Cliente obtenido', ['client' => $client]);

        $taxRates = TaxRate::all();

        if (Auth::user()->can('view_all_price-lists')) {
            $priceLists = PriceList::all();
        } else {
            $priceLists = PriceList::where('store_id', Auth::user()->store_id)->get();
        }

        // Obtener los vendedores disponibles
        $vendors = Vendor::select('id', 'name', 'lastname')->get();

        // Obtener la primera lista de precios asignada al cliente, o mostrar un mensaje si no hay lista asignada
        $priceListName = $client->priceLists->isNotEmpty()
            ? $client->priceLists->first()->name
            : 'Sin lista de precios';

        return view('content.clients.show', compact('client', 'priceLists', 'priceListName', 'taxRates', 'vendors'));
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
        $taxRates = TaxRate::all();

        return view('content.clients.edit', compact('client', 'orders', 'taxRates'));
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
        try {
            Log::info('Iniciando actualización del cliente', ['client_id' => $id]);

            // Buscar el cliente por ID
            $client = Client::findOrFail($id);
            Log::info('Cliente encontrado', ['client' => $client->id]);

            // Actualizar los datos del cliente
            $client->update($request->validated());

            // Verificar si se ha proporcionado un `price_list_id`
            if ($request->has('price_list_id')) {
                $priceListId = $request->input('price_list_id');

                // Asegurarse de que el `price_list_id` exista en la tabla `price_lists`
                $priceListExists = DB::table('price_lists')->where('id', $priceListId)->exists();

                if ($priceListExists) {
                    // Crear o actualizar la relación en la tabla pivot `client_price_lists`
                    DB::table('client_price_lists')
                        ->updateOrInsert(
                            ['client_id' => $client->id], // Condición para encontrar la relación
                            ['price_list_id' => $priceListId] // Actualización o inserción del `price_list_id`
                        );
                } else {
                    return response()->json(['success' => false, 'message' => 'La lista de precios seleccionada no existe.'], 404);
                }
            } else {
                Log::info('No se ha recibido price_list_id');
            }

            // Verificar si se ha proporcionado un `vendor_id`
            if ($request->has('vendor_id')) {
                $vendorId = $request->input('vendor_id');
                $client->vendor_id = $vendorId;
                $client->save();
            }

            return response()->json(['success' => true, 'message' => 'Cliente y lista de precios actualizados correctamente.']);
        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar el cliente: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Elimina un cliente específico de la base de datos.
     *
     * @param int $id
     * @return RedirectResponse | JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Intentar eliminar el cliente usando el repositorio
            $this->clientRepository->deleteClient($id);

            return response()->json(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        } catch (\Illuminate\Database\QueryException $e) {
            // Manejar violaciones de restricciones de clave foránea
            if ($e->getCode() === '23000') { // Código para Integrity Constraint Violation
                return response()->json(['success' => false, 'message' => 'No se puede eliminar el cliente porque está asociado a una cuenta corriente.'], 400);
            }

            // Manejar otros errores inesperados
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al intentar eliminar el cliente.'], 500);
        }
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

    public function getProductsByPriceList($priceListId)
    {
        // Obtener los productos y el precio específico de la lista de precios desde la tabla price_list_products
        $products = Product::select('products.id', 'products.name', 'price_list_products.price')
            ->join('price_list_products', 'products.id', '=', 'price_list_products.product_id')
            ->where('price_list_products.price_list_id', $priceListId)
            ->get();

        return response()->json(['products' => $products]);
    }

    public function getClientPriceList($clientId)
    {
        // Buscar el cliente por ID
        $client = Client::with('priceLists')->find($clientId);

        if (!$client) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Inicializamos $priceListName como 'Sin lista de precios '
        $priceListId = null;
        $priceListName = 'Sin lista de precios';

        // Verificar si el cliente tiene una lista de precios asociada
        if ($client->priceLists->isNotEmpty()) {
            $priceListId = $client->priceLists->first()->id; // Obtenemos el primer ID de lista de precios
            $priceListName = $client->priceLists->first()->name; // Obtenemos el nombre de la lista de precios
        }

        $rut = $client->rut;
        $ci = $client->ci;

        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'lastname' => $client->lastname,
                'type' => $client->type,
                'company_name' => $client->company_name,
                'price_list_id' => $priceListId,
                'price_list_name' => $priceListName,
                'ci' => $ci,
                'rut' => $rut
            ]
        ]);
    }

    public function getPriceLists($clientId)
    {
        try {
            // Registrar en el log el ID del cliente que estamos intentando obtener
            Log::info("Intentando obtener lista de precios para el cliente con ID: {$clientId}");

            // Buscar el cliente por su ID, cargando también la relación con la lista de precios
            $client = Client::with('priceList')->findOrFail($clientId);
            Log::info("Cliente encontrado: ", ['client' => $client]);

            // Verificar si el cliente tiene una lista de precios asignada
            if ($client->priceList) {
                Log::info("Lista de precios encontrada: ", ['price_list' => $client->priceList]);

                return response()->json([
                    'status' => 'success',
                    'price_list' => [
                        'id' => $client->priceList->id,
                        'name' => $client->priceList->name,
                    ]
                ], 200);
            } else {
                Log::warning("El cliente con ID: {$clientId} no tiene una lista de precios asignada.");

                return response()->json([
                    'status' => 'error',
                    'message' => 'El cliente no tiene una lista de precios asignada'
                ], 404);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Cliente con ID: {$clientId} no encontrado. Excepción: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Cliente no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Error inesperado al obtener la lista de precios para el cliente con ID: {$clientId}. Excepción: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener la lista de precios: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assignPriceList(UpdateClientRequest $request, $clientId)
    {
        // Buscar el cliente por su ID
        $client = Client::findOrFail($clientId);

        // Validar que la lista de precios es válida y está presente
        if ($request->has('price_list_id')) {
            $request->validate([
                'price_list_id' => 'required|exists:price_lists,id',
            ]);

            // Asignar o actualizar la lista de precios en la tabla pivot client_price_lists
            $client->priceLists()->sync([$request->input('price_list_id')]);
        }

        // Actualizar los datos del cliente (esto ya está cubierto por UpdateClientRequest)
        $client->update($request->validated());

        return response()->json(['success' => 'Cliente y lista de precios actualizados correctamente.']);
    }

     /**
     * Descarga una plantilla para importar clientes.
     *
     * @param Request $request
     * @return mixed
     */
    public function downloadTemplate(Request $request)
    {
        try {
            return Excel::download(new ClientTemplateImport, 'plantilla_clientes.xlsx');
        } catch (\Exception $e) {
            Log::error('Error al descargar la plantilla: ' . $e->getMessage());
            return back()->with('error', 'Error al descargar la plantilla');
        }
    }


    /**
     * Importa clientes desde un archivo Excel.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            // Obtener store_id del usuario autenticado
            $store_id = Auth::user()->store_id;

            DB::beginTransaction();

            Excel::import(new ClientImport($store_id), $request->file('file'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Clientes importados correctamente'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = "Fila {$failure->row()}: {$failure->errors()[0]}";
            }

            Log::error('Error en la validación de importación: ', $errors);

            return response()->json([
                'success' => false,
                'message' => 'Error en la validación de datos',
                'errors' => $errors
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al importar clientes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al importar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar los clientes a un archivo Excel.
     *
     */
    public function export()
    {
        return Excel::download(new ClientsExport, 'clientes.xlsx');
    }

}
