<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCashRegisterLogRequest;
use App\Http\Requests\UpdateCashRegisterLogRequest;
use App\Repositories\CashRegisterLogRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreClientRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class CashRegisterLogController extends Controller
{

    protected $cashRegisterLogRepository;

    public function __construct(CashRegisterLogRepository $cashRegisterLogRepository)
    {
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pdv.index');
    }

    public function front()
    {
      $products = Product::all();
      return view('pdv.front', compact('products'));
    }


    public function front2()
    {
      return view('pdv.front2');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Agrega un log de caja registradora a la base de datos.
     * La función del método es abrir la caja registradora ese día.
     *
     * @param StoreCashRegisterLogRequest $request
     * @param JsonResponse
     */
    public function store(StoreCashRegisterLogRequest $request)
    {

        $cashRegisterId = $request->input('cash_register_id');

        // Verificar si hay un log existente sin fecha de cierre
        if ($this->cashRegisterLogRepository->hasOpenLogForUser(Auth::id())) {
            return response()->json(['message' => 'Ya existe una caja registradora abierta para este usuario.'], 400);
        }

        $request['open_time'] = now();
        $request['cash_sales'] = 0;
        $request['pos_sales'] = 0;
        $validatedData = $request->validated();
        $cashRegisterLog = $this->cashRegisterLogRepository->createCashRegisterLog($validatedData);
        Session::put('open_cash_register_id', $cashRegisterId);
        return response()->json($cashRegisterLog, 201);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Actualiza un log de una caja registradora.
     *
     * @param UpdateCashRegisterLogRequest $request
     * @param string $id
     */
    public function update(UpdateCashRegisterLogRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $updated = $this->cashRegisterLogRepository->updateCashRegisterLog($id, $validatedData);

        if ($updated) {
            return response()->json(['message' => 'Cash register log updated successfully.']);
        } else {
            return response()->json(['message' => 'Cash register log not found or not updated.'], 404);
        }
    }

    /**
     * Borra un log de caja registradora dado un id.
     *
     * @param string $id
     */
    public function destroy(string $id)
    {
        $deleted = $this->cashRegisterLogRepository->deleteCashRegisterLog($id);

        if ($deleted) {
            return response()->json(['message' => 'Log de caja registradora borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el log de la caja registradora que se deseó borrar.'], 404);
        }
    }


    /**
     * Cierre de caja.
     *
     * @param string $id
     */
    public function closeCashRegister(string $id)
    {
        $closed = $this->cashRegisterLogRepository->closeCashRegister($id);

        if ($closed) {
            Session::forget('open_cash_register_id');
            return response()->json(['message' => 'Caja registradora cerrada correctamente.']);
        } else {
            return response()->json(['message' => 'Ha ocurrido un error intentando cerrar la caja registradora.'], 404);
        }
    }


    /**
     * Toma los productos de la empresa de la caja registradora.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsByCashRegister(int $id)
    {
        $products = $this->cashRegisterLogRepository->getAllProductsForPOS($id);
        return response()->json(['products' => $products]);
    }

    /**
     * Toma los productos de la empresa de la caja registradora.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFlavorsForCashRegister()
    {
        try {
            $flavors = $this->cashRegisterLogRepository->getFlavors();
            return response()->json(['flavors' => $flavors]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }


    /**
     * Toma las categorías padres.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFathersCategories()
    {
        try {
            $categories = $this->cashRegisterLogRepository->getFathersCategories();
            return response()->json(['categories' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Toma las categorías padres.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        try {
            $productCategories = $this->cashRegisterLogRepository->getCategories();
            return response()->json($productCategories);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }


    /**
   * Almacena un nuevo cliente en la base de datos.
   *
   * @param StoreClientRequest $request
   * @return JsonResponse
  */
  public function storeClient(StoreClientRequest $request): JsonResponse
  {
    try {
      $validatedData = $request->validated();

      // Establecer valores predeterminados si no están presentes en la solicitud
      $validatedData['address'] = $validatedData['address'] ?? '-';
      $validatedData['city'] = $validatedData['city'] ?? '-';
      $validatedData['state'] = $validatedData['state'] ?? '-';
      $validatedData['country'] = $validatedData['country'] ?? '-';
      $validatedData['phone'] = $validatedData['phone'] ?? '-';

      // Crear el nuevo cliente
      $newClient = $this->cashRegisterLogRepository->createClient($validatedData);

      // Agregar log
      Log::info('Nuevo cliente creado desde PDV', [
          'client_id' => $newClient->id,
          'name' => $newClient->name,
          'email' => $newClient->email,
          'type' => $newClient->type
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Cliente creado correctamente.',
        'client' => $newClient  // Devuelve los datos completos del cliente
    ]);
    } catch (\Exception $e) {
        Log::error('Error al crear cliente desde PDV: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Ocurrió un error al crear el cliente.']);
    }
  }

    /**
     * Busca el id del cashregister log y el store_id dado un id de caja registradora.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCashRegisterLog(string $id)
    {
        try {
            $result = $this->cashRegisterLogRepository->getCashRegisterLogWithStore($id);
            if ($result === null) {
                return response()->json(['error' => 'No open log found'], 404);
            }
            return response()->json($result); // Devuelve tanto el cash_register_log_id como el store_id
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Obtiene todos los clientes en formato JSON.
     *
     * @return JsonResponse
     */
    public function getAllClients(): JsonResponse
    {
        $clients = $this->cashRegisterLogRepository->getAllClients();
        return response()->json([
            'clients' => $clients,
            'count' => $clients->count()
        ]);
    }

    /**
     * Guarda el carrito del PDV de la session.
     *
     * @return JsonResponse
     */
    public function saveCart(Request $request)
    {
        $cart = $request->input('cart');
        session(['cart' => $cart]);
        return response()->json(['status' => 'success']);
    }

    /**
     * Devuelve el carrito del PDV de la session.
     *
     * @return JsonResponse
     */
    public function getCart()
    {
        $cart = session('cart', []);
        return response()->json(['cart' => $cart]);
    }

     /**
     * Guarda el cliente del PDV de la session.
     *
     * @return JsonResponse
     */
    public function saveClient(Request $request)
    {
        $client = $request->input('client');
        session(['client' => $client]);
        return response()->json(['status' => 'success']);
    }

    /**
     * Devuelve el cliente del PDV de la session.
     *
     * @return JsonResponse
     */
    public function getClient()
    {
        $client = session('client', []);
        return response()->json(['client' => $client]);
    }

    /**
     * Devuelve el Store ID de la session.
     *
     * @return JsonResponse
     */
    public function getStoreId()
    {
        $id = session('store_id', []);
        return response()->json(['id' => $id]);
    }
}
