<?php

namespace App\Repositories;

use App\Models\CashRegisterLog;
use Yajra\DataTables\DataTables;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\CompositeProduct;
use App\Models\ProductCategory;
use App\Models\CashRegister;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Services\EmailService;
use Illuminate\Support\Facades\Auth;



class CashRegisterLogRepository
{

    protected $companySettings;

    public function __construct($companySettings)
    {
        // Asigna companySettings al repositorio
        $this->companySettings = $companySettings;
    }


    public function hasOpenLogForUser(int $userId): ?int
    {
        $openLog = CashRegisterLog::whereNull('close_time')
            ->whereHas('cashRegister', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();

        return $openLog ? $openLog->cash_register_id : null;
    }


    /**
     * Obtiene todos los registros del log de una caja dado su ID.
     *
     * @param int $id
     * @return CashRegisterLog|null
    */
    public function getLogsFromACashRegister(int $id): ?CashRegisterLog
    {
        return CashRegisterLog::find($id);
    }

    /**
     * Actualiza un registro de caja existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
    */
    public function updateCashRegisterLog(int $id, array $data): bool
    {
        $cashRegisterLog = CashRegisterLog::find($id);
        if ($cashRegisterLog) {
            return $cashRegisterLog->update($data);
        }
        return false;
    }

    /**
     * Elimina un registro de log de una caja por su ID.
     *
     * @param int $id
     * @return bool
    */
    public function deleteCashRegisterLog(int $id): bool
    {
        $cashRegisterLog = CashRegisterLog::find($id);
        if ($cashRegisterLog) {
            return $cashRegisterLog->delete();
        }
        return false;
    }

    /**
     * Crea un nuevo registro de log de una caja registradora.
     *
     * @param array $data
     * @return CashRegisterLog
    */
    public function createCashRegisterLog(array $data): CashRegisterLog
    {
        if (!isset($data['open_time']) || !$data['open_time'] instanceof \Carbon\Carbon) {
            $data['open_time'] = now();
        }
        $cashRegisterLog = CashRegisterLog::create($data);

        /*
        $emailService = new EmailService();
        $variables = [
            'cash_register_id' => $cashRegisterLog->cash_register_id,
            'employee_name' => $cashRegisterLog->employee_name,
            'open_time' => $cashRegisterLog->open_time
        ];
        $emailService->sendCashRegisterOpenedEmail($variables);
        */
        return $cashRegisterLog;
    }

    /**
    * Cierra la caja registradora.
    *
    * @param Store $store
    * @return RedirectResponse
    */
    public function closeCashRegister(int $id): ?bool
    {
        $openLog = CashRegisterLog::where('cash_register_id', $id)
            ->whereNull('close_time')
            ->first();

        if (!$openLog || $openLog->close_time) {
            return false;
        }

        try {
            // Cerrar caja
            $openLog->close_time = now();
            $openLog->save();

            // Convertir open_time y close_time a formato de cadena
            $openTime = $openLog->open_time;
            $closeTime = $openLog->close_time;

            // Calcular ventas en efectivo y POS
            $totalSales = \DB::table('pos_orders')
                ->selectRaw('SUM(cash_sales) as total_cash_sales, SUM(pos_sales) as total_pos_sales')
                ->where('cash_register_log_id', $openLog->id)
                ->whereBetween(\DB::raw('CONCAT(date, " ", hour)'), [$openTime, $closeTime])
                ->first();

            if ($totalSales) {
                $openLog->cash_sales = $totalSales->total_cash_sales ?? 0;
                $openLog->pos_sales = $totalSales->total_pos_sales ?? 0;
            }

            $openLog->save();

            // Enviar notificación por correo electrónico
            /*
            $emailService = new EmailService();
            $variables = [
                'cash_register_id' => $id,
                'employee_name' => $openLog->employee_name,
                'close_time' => $closeTime,
                'cash_sales' => $openLog->cash_sales,
                'pos_sales' => $openLog->pos_sales
            ];
            $emailService->sendCashRegisterClosedEmail($variables);
            */
            return true;
        } catch (\Exception $e) {
            \Log::error('Error closing cash register: ' . $e->getMessage());
            return false;
        }
    }




    /**
     * Verifica si hay un log abierto para una caja registradora específica.
     *
     * @param int $cashRegisterId
     * @return bool
     */
    public function hasOpenLog(): bool
    {
        return CashRegisterLog::whereNull('close_time')
            ->exists();
    }

    /**
     * Toma los productos de la tienda de la caja registradora, incluyendo productos compuestos.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProductsForPOS(int $id)
    {
        $cashRegister = CashRegister::find($id);

        if (!$cashRegister) {
            throw new \Exception('Cash register not found');
        }

        $storeId = $cashRegister->store_id;

        // Obtener los productos de la tabla products y agregar el campo 'type'
        $products = Product::where('store_id', $storeId)
            ->get()
            ->map(function ($product) {
                $product->is_composite = 0; // Agregar un campo 'type' indicando que es un producto normal
                return $product;
            });

        // Obtener los productos compuestos de la tabla composite_products y agregar el campo 'type'
        $compositeProducts = CompositeProduct::where('store_id', $storeId)
            ->get()
            ->map(function ($compositeProduct) {
                $compositeProduct->is_composite = 1; // Agregar un campo 'type' indicando que es un producto compuesto
                return $compositeProduct;
            });

        // Combinar ambos conjuntos de productos en una única colección
        $allProducts = $products->merge($compositeProducts);

        return $allProducts;
    }


    /**
     * Toma los variaciones para crear los productos con varios variaciones.
     *
     * @return Flavor
     */
    public function getFlavors()
    {
        $flavors = Flavor::all();
        return $flavors;
    }


    /**
     * Toma las categorías padres.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFathersCategories()
    {
        return DB::table('category_product')->get();
    }

    /**
     * Toma las categorías padres.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        return DB::table('product_categories')->get();
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
     * Busca el ID del registro de caja dado un ID de caja registradora y devuelve el store_id.
     *
     * @param string $id
     *
     * @return array|null
     */
    public function getCashRegisterLogWithStore(string $id)
    {
        $openLog = CashRegisterLog::where('cash_register_id', $id)
            ->whereNull('close_time')
            ->first();

        if ($openLog) {
            $cashRegister = $openLog->cashRegister; // Relación con CashRegister (asegúrate de tener la relación definida)
            return [
                'cash_register_log_id' => $openLog->id,
                'store_id' => $cashRegister->store_id
            ];
        }

        return null;
    }


    /**
     * Obtiene todos los clientes según la configuración de clients_has_store.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllClients(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->companySettings && $this->companySettings->clients_has_store == 1) {
            // Filtrar los clientes que tienen el mismo store_id que el usuario autenticado
            return Client::select('id', 'name', 'lastname', 'ci', 'rut', 'type', 'company_name', 'phone', 'address', 'email')
                ->where('store_id', Auth::user()->store_id)  // Filtra por store_id del usuario autenticado
                ->get()
                ->map(function ($client) {
                    $client->ci = $client->ci ?? 'No CI';
                    $client->rut = $client->rut ?? 'No RUT';
                    return $client;
                });
        } else {
            // Si clients_has_store es 0, mostrar todos los clientes
            return Client::select('id', 'name', 'lastname', 'ci', 'rut', 'type', 'company_name', 'phone', 'address', 'email')
                ->get()
                ->map(function ($client) {
                    $client->ci = $client->ci ?? 'No CI';
                    $client->rut = $client->rut ?? 'No RUT';
                    return $client;
                });
        }
    }


}
