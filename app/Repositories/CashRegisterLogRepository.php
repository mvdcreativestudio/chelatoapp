<?php

namespace App\Repositories;

use App\Models\CashRegisterLog;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\CompositeProduct;
use App\Models\ProductCategory;
use App\Models\CashRegister;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use App\Enums\Events\EventEnum;
use App\Services\EventHandlers\EventService;
use Illuminate\Support\Facades\Log;

class CashRegisterLogRepository
{

    protected $companySettings;
    protected $eventService;

    public function __construct($companySettings, $eventService = null)
    {
        $this->companySettings = $companySettings;
        $this->eventService = $eventService;
    }

    /**
     * Verifica si el usuario tiene un log abierto en una tienda específica.
     */
    public function hasOpenLogForUserInStore(int $userId, int $storeId): ?int
    {
        $openLog = CashRegisterLog::whereNull('close_time')
            ->whereHas('cashRegister', function ($query) use ($userId, $storeId) {
                $query->where('user_id', $userId)
                      ->where('store_id', $storeId);
            })
            ->first();

        return $openLog ? $openLog->cash_register_id : null;
    }

    /**
     * Verifica si el usuario tiene un log abierto (cualquier tienda).
     */
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
     * Obtiene todas las cajas abiertas para un usuario.
     */
    public function getOpenCashRegistersForUser(int $userId)
    {
        return CashRegisterLog::whereNull('close_time')
            ->whereHas('cashRegister', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with('cashRegister.store')
            ->get();
    }

    /**
     * Obtiene todos los registros del log de una caja dado su ID.
     */
    public function getLogsFromACashRegister(int $id): ?CashRegisterLog
    {
        return CashRegisterLog::find($id);
    }

    /**
     * Actualiza un registro de caja existente.
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
     */
    public function createCashRegisterLog(array $data): CashRegisterLog
    {
        if (!isset($data['open_time']) || !$data['open_time'] instanceof \Carbon\Carbon) {
            $data['open_time'] = now();
        }
        $cashRegisterLog = CashRegisterLog::create($data);
        return $cashRegisterLog;
    }

    /**
     * Cierra la caja registradora calculando ventas por método de pago y gastos.
     */
    public function closeCashRegister(int $id, ?float $actualCash = null, float $cashDifference = 0): ?bool
    {
        $openLog = CashRegisterLog::where('cash_register_id', $id)
            ->whereNull('close_time')
            ->first();

        if (!$openLog || $openLog->close_time) {
            Log::warning("No se encontró log abierto para la caja ID: $id o ya estaba cerrada.");
            return false;
        }

        try {
            $openTime = $openLog->open_time;
            $closeTime = now();

            // Calcular ventas por método de pago (excluyendo reembolsadas, descontando NC en parciales)
            $totalSales = DB::table('orders')
                ->selectRaw("
                    SUM(CASE WHEN payment_method = 'cash' THEN
                        CASE WHEN payment_status = 'partial_refunded' THEN
                            total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                        ELSE total END
                    ELSE 0 END) as total_cash_sales,
                    SUM(CASE WHEN payment_method IN ('credit', 'debit', 'card') THEN
                        CASE WHEN payment_status = 'partial_refunded' THEN
                            total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                        ELSE total END
                    ELSE 0 END) as total_pos_sales,
                    SUM(CASE WHEN payment_method = 'mercadopago' THEN
                        CASE WHEN payment_status = 'partial_refunded' THEN
                            total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                        ELSE total END
                    ELSE 0 END) as total_mercadopago_sales,
                    SUM(CASE WHEN payment_method = 'bankTransfer' THEN
                        CASE WHEN payment_status = 'partial_refunded' THEN
                            total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                        ELSE total END
                    ELSE 0 END) as total_bank_transfer_sales,
                    SUM(CASE WHEN payment_method = 'internalCredit' THEN
                        CASE WHEN payment_status = 'partial_refunded' THEN
                            total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                        ELSE total END
                    ELSE 0 END) as total_internal_credit_sales
                ")
                ->where('cash_register_log_id', $openLog->id)
                ->where('payment_status', '!=', 'refunded')
                ->first();

            // Actualizar ventas por cada método de pago
            $openLog->cash_sales = $totalSales->total_cash_sales ?? 0;
            $openLog->pos_sales = $totalSales->total_pos_sales ?? 0;
            $openLog->mercadopago_sales = $totalSales->total_mercadopago_sales ?? 0;
            $openLog->bank_transfer_sales = $totalSales->total_bank_transfer_sales ?? 0;
            $openLog->internal_credit_sales = $totalSales->total_internal_credit_sales ?? 0;

            // Calcular gastos convertidos a pesos
            $expenses = \App\Models\Expense::where('cash_register_log_id', $openLog->id)->get();
            $totalExpensesInPesos = 0;

            foreach ($expenses as $expense) {
                if ($expense->currency === 'Dólar') {
                    $rate = $expense->currency_rate ?? $this->getDollarExchangeRate();
                    $totalExpensesInPesos += $expense->amount * $rate;
                } else {
                    $totalExpensesInPesos += $expense->amount;
                }
            }

            $openLog->cash_expenses = $totalExpensesInPesos;

            // Guardar efectivo real y diferencia si se proporcionan
            if ($actualCash !== null) {
                $openLog->actual_cash = $actualCash;
                $openLog->cash_difference = $cashDifference;
            }

            // Cerrar la caja
            $openLog->close_time = $closeTime;
            $saved = $openLog->save();

            if (!$saved) {
                Log::error("Error al guardar el cierre de caja para ID: $id");
                return false;
            }

            Log::info("Caja cerrada exitosamente", [
                'cash_register_id' => $id,
                'log_id' => $openLog->id,
                'cash_sales' => $openLog->cash_sales,
                'pos_sales' => $openLog->pos_sales,
                'mercadopago_sales' => $openLog->mercadopago_sales,
                'bank_transfer_sales' => $openLog->bank_transfer_sales,
                'internal_credit_sales' => $openLog->internal_credit_sales,
                'cash_expenses' => $openLog->cash_expenses,
                'cash_float' => $openLog->cash_float
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al cerrar caja ID: $id - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la cotización actual del dólar.
     */
    private function getDollarExchangeRate(): float
    {
        try {
            $dollarRate = \App\Models\CurrencyRate::where('name', 'Dólar')->first();

            if (!$dollarRate) {
                return 1;
            }

            $latestRate = \App\Models\CurrencyRateHistory::where('currency_rate_id', $dollarRate->id)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            return $latestRate ? $latestRate->sell : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Envía un correo con el resumen del cierre de caja.
     */
    public function sendCashRegisterClosedEmail(int $cashRegisterId)
    {
        try {
            if (!$this->eventService) {
                return;
            }

            $cashRegisterLog = CashRegisterLog::where('cash_register_id', $cashRegisterId)
                ->whereNotNull('close_time')
                ->latest()
                ->first();

            if (!$cashRegisterLog) {
                Log::error("No se encontró información de la caja cerrada para el ID: " . $cashRegisterId);
                return;
            }

            // Intentar enviar evento si existe el caso CASH_REGISTER_CLOSED en EventEnum
            $enumClass = new \ReflectionEnum(EventEnum::class);
            if ($enumClass->hasCase('CASH_REGISTER_CLOSED')) {
                $this->eventService->handleEvents(
                    auth()->user()->store_id,
                    [EventEnum::from('cash_register_closed')],
                    ['cash_register_id' => $cashRegisterId]
                );
            }
        } catch (\Exception $e) {
            Log::warning("No se pudo enviar email de cierre de caja: " . $e->getMessage());
        }
    }

    /**
     * Verifica si hay un log abierto para una caja registradora específica.
     */
    public function hasOpenLog(): bool
    {
        return CashRegisterLog::whereNull('close_time')
            ->exists();
    }

    /**
     * Toma los productos de la tienda de la caja registradora, incluyendo productos compuestos.
     */
    public function getAllProductsForPOS(int $id)
    {
        $cashRegister = CashRegister::find($id);

        if (!$cashRegister) {
            throw new \Exception('Cash register not found');
        }

        $storeId = $cashRegister->store_id;

        $products = Product::where('store_id', $storeId)
            ->where('is_trash', 0)
            ->where('status', 1)
            ->get()
            ->map(function ($product) {
                $product->is_composite = 0;
                $product->image = $product->image ? asset($product->image) : asset('assets/img/ecommerce-images/placeholder.png');
                return $product;
            });

        $compositeProducts = CompositeProduct::where('store_id', $storeId)
            ->get()
            ->map(function ($compositeProduct) {
                $compositeProduct->is_composite = 1;
                $compositeProduct->image = $compositeProduct->image ? asset($compositeProduct->image) : asset('assets/img/ecommerce-images/placeholder.png');
                return $compositeProduct;
            });

        $allProducts = $products->merge($compositeProducts);

        return $allProducts;
    }

    /**
     * Toma las variaciones para crear los productos con varias variaciones.
     */
    public function getFlavors()
    {
        return Flavor::all();
    }

    /**
     * Toma las categorías padres.
     */
    public function getFathersCategories()
    {
        return DB::table('category_product')->get();
    }

    /**
     * Toma las categorías de productos.
     */
    public function getCategories()
    {
        return DB::table('product_categories')->get();
    }

    /**
     * Crea un nuevo cliente.
     */
    public function createClient(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Busca el ID del registro de caja dado un ID de caja registradora y devuelve el store_id.
     */
    public function getCashRegisterLogWithStore(string $id)
    {
        $openLog = CashRegisterLog::where('cash_register_id', $id)
            ->whereNull('close_time')
            ->first();

        if ($openLog) {
            $cashRegister = $openLog->cashRegister;
            $operator = $cashRegister->user;
            return [
                'cash_register_log_id' => $openLog->id,
                'store_id' => $cashRegister->store_id,
                'cash_register_id' => $openLog->cash_register_id,
                'operator_name' => $operator ? $operator->name : 'No disponible',
                'cash_balance' => $openLog->getFinalCashBalance(),
                'open_time' => $openLog->open_time ? $openLog->open_time->format('d/m/Y H:i') : 'No disponible'
            ];
        }

        return null;
    }

    /**
     * Obtiene todos los clientes según la configuración de clients_has_store.
     */
    public function getAllClients(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->companySettings && $this->companySettings->clients_has_store == 1) {
            return Client::select('id', 'name', 'lastname', 'ci', 'rut', 'type', 'company_name', 'phone', 'address', 'email', 'branch')
                /* ->with('priceLists:id,name') */
                ->where('store_id', Auth::user()->store_id)
                ->get()
                ->map(function ($client) {
                    $client->ci = $client->ci ?? 'No CI';
                    $client->rut = $client->rut ?? 'No RUT';
                    return $client;
                });
        } else {
            return Client::select('id', 'name', 'lastname', 'ci', 'rut', 'type', 'company_name', 'phone', 'address', 'email', 'branch')
                /* ->with('priceLists:id,name') */
                ->get()
                ->map(function ($client) {
                    $client->ci = $client->ci ?? 'No CI';
                    $client->rut = $client->rut ?? 'No RUT';
                    return $client;
                });
        }
    }

    /**
     * Obtiene una consulta de clientes con opciones de búsqueda.
     */
    public function getClientsQuery(?string $search = ''): \Illuminate\Database\Eloquent\Builder
    {
        $search = $search ?? '';

        $query = Client::select('id', 'name', 'lastname', 'ci', 'rut', 'type', 'company_name', 'phone', 'address', 'email', 'branch')
            /* ->with('priceLists:id,name') */;

        if ($this->companySettings && $this->companySettings->clients_has_store == 1) {
            $query->where('store_id', Auth::user()->store_id);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('ci', 'like', "%{$search}%")
                  ->orWhere('rut', 'like', "%{$search}%")
                  ->orWhere('branch', 'like', "%{$search}%");
            });
        }

        return $query->orderByRaw("
            CASE
                WHEN type = 'company' THEN company_name
                ELSE CONCAT(COALESCE(name, ''), ' ', COALESCE(lastname, ''))
            END
        ");
    }
}
