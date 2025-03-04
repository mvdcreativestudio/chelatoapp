<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCashRegisterLogRequest;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateCashRegisterLogRequest;
use App\Models\CurrencyRateHistory;
use App\Models\CurrencyRate;
use App\Models\MercadoPagoAccountPOS;
use App\Models\Product;
use App\Models\TaxRate;
use App\Repositories\CashRegisterLogRepository;
use App\Repositories\CashRegisterRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PriceList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CashRegisterLogController extends Controller
{

    protected $cashRegisterLogRepository;
    protected $cashRegisterRepository;

    public function __construct(CashRegisterLogRepository $cashRegisterLogRepository, CashRegisterRepository $cashRegisterRepository, )
    {
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
        $this->cashRegisterRepository = $cashRegisterRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pdv.index');
    }

    public function cart()
    {
        $products = Product::all();
        $taxRates = TaxRate::all();
        $userId = auth()->user()->id;
        $openCashRegisterId = $this->cashRegisterLogRepository->hasOpenLogForUser($userId);
        $storeId = $this->cashRegisterRepository->findStoreByCashRegisterId($openCashRegisterId);
        Session::put('open_cash_register_id', $openCashRegisterId);
        Session::put('store_id', $storeId);
        $priceLists = PriceList::all();
        return view('pdv.cart', compact('products', 'priceLists', 'taxRates'));
    }


    public function checkout()
    {
        $openCashRegisterId = Session::get('open_cash_register_id');

        if (!$openCashRegisterId) {
            Log::warning('No hay una caja registradora abierta.');
            return view('pdv.checkout', [
                'priceLists' => PriceList::all(),
                'enableMercadoPagoAccountPos' => false,
                'posDeviceName' => null,
                'store' => null,
            ]);
        }

        // Obtener el modelo completo de Store
        $store = $this->cashRegisterRepository->findStoreByCashRegisterId($openCashRegisterId);

        // Consultar el nombre del dispositivo POS asociado al cash_register_id
        $posDevice = DB::table('cash_register_pos_device')
            ->where('cash_register_id', $openCashRegisterId)
            ->join('pos_devices', 'cash_register_pos_device.pos_device_id', '=', 'pos_devices.id')
            ->select('pos_devices.name as pos_device_name')
            ->first();

        $posDeviceName = $posDevice ? $posDevice->pos_device_name : null;
        $enableMercadoPagoAccountPos = MercadoPagoAccountPOS::where('cash_register_id', $openCashRegisterId)->exists();
        $priceLists = PriceList::all();
        $taxRates = TaxRate::all();

        return view('pdv.checkout', compact('store', 'priceLists', 'enableMercadoPagoAccountPos', 'posDeviceName', 'taxRates'));
    }

    public function taxRates()
    {
        $taxRates = TaxRate::all();
        return response()->json(['taxRates' => $taxRates]);
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
        return $this->cashRegisterLogRepository->deleteCashRegisterLog($id);
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

            // Verificar si se ha proporcionado una lista de precios y guardarla en la tabla `client_price_lists`
            if (isset($validatedData['price_list_id'])) {
                DB::table('client_price_lists')->insert([
                    'client_id' => $newClient->id,
                    'price_list_id' => $validatedData['price_list_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado correctamente.',
                'client' => $newClient

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
            'count' => $clients->count(),
        ]);
    }

   /**
   * Guarda el carrito del PDV en la sesión. Si un producto tiene 'currency':'Dólar',
   * se usa la última cotización y multiplica por el valor de venta del Dólar.
   *
   * @return JsonResponse
   */
  public function saveCart(Request $request)
  {
      $cart = $request->input('cart');

      // Validación: si el carrito es null o vacío, no lo guarda en la sesión
      if (!is_array($cart) || empty($cart)) {
          Log::warning('⚠️ Intento de guardar un carrito vacío en sesión. No se guardará.', ['cart' => $cart]);
          return response()->json(['warning' => 'El carrito está vacío, no se guardará en la sesión'], 200);
      }

      try {
          $dollar = CurrencyRate::where('name', 'Dólar')->first();
          $rate = CurrencyRateHistory::where('currency_rate_id', $dollar->id)
              ->orderBy('created_at', 'desc')
              ->first();

          foreach ($cart as &$item) {
              if (isset($item['currency']) && ($item['currency'] === 'Dólar' || $item['currency'] === 'D\u00f3lar')) {
                  $item['exchange_price'] = $item['price'] / $rate->sell;
              }
          }

          Log::info('✅ Carrito guardado en sesión:', ['cart' => $cart]);
          session(['cart' => $cart]);

          return response()->json(['status' => 'success']);

      } catch (\Exception $e) {
          Log::error('❌ Error al guardar el carrito en la sesión', ['error' => $e->getMessage()]);
          return response()->json(['error' => 'Error al guardar el carrito en la sesión'], 500);
      }
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

    /*
    * Devuelve la última cotización del Dólar.
    */
    public function getExchangeRate()
    {
        $dollar = CurrencyRate::where('name', 'Dólar')->first();
        $rate = CurrencyRateHistory::where('currency_rate_id', $dollar->id)
            ->orderBy('created_at', 'desc')
            ->first();
        return response()->json(['exchange_rate' => $rate]);
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
