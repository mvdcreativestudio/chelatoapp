<?php

namespace App\Repositories;

use App\Models\Budget;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\SelectStoreRequest;
use App\Models\BudgetItem;
use App\Models\BudgetStatus;
use App\Models\Client;
use App\Models\PriceList;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Illuminate\Support\Str;


class BudgetRepository
{
    /**
     * Obtiene todos los presupuestos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return Budget::all();
    }

    /**
     * Obtiene un presupuesto por su ID.
     *
     * @param int $id
     * @return Budget
     */
    public function findById($id)
    {
        return Budget::findOrFail($id);
    }

    /**
     * Crea un nuevo presupuesto.
     *
     * @param array $data
     * @return Budget
     */
    public function create(array $data)
    {
        return Budget::create($data);
    }

    /**
     * Actualiza un presupuesto existente.
     *
     * @param Budget $budget
     * @param array $data
     * @return array
     */
    public function update(Budget $budget, array $data): array
    {
        try {
            Log::info('Iniciando actualización de presupuesto:', ['budget_id' => $budget->id]);
            Log::info('Datos recibidos:', $data);

            DB::beginTransaction();

            // Actualizar datos básicos del presupuesto
            $budget->update([
                'client_id' => $data['client_id'],
                'lead_id' => $data['lead_id'],
                'price_list_id' => $data['price_list_id'],
                'store_id' => $data['store_id'],
                'due_date' => $data['due_date'],
                'notes' => $data['notes'],
                'discount_type' => $data['discount_type'],
                'discount' => $data['discount'],
                'is_blocked' => $data['is_blocked'] ?? false,
                'total' => $data['total']
            ]);

            // Eliminar items existentes
            $budget->items()->delete();

            // Crear nuevos items
            foreach ($data['items'] as $productId => $item) {
                $product = Product::findOrFail($productId);
                
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $product->build_price,
                    'discount_type' => $item['discount_type'] ?: null,
                    'discount_price' => $item['discount'] ?? 0
                ]);
            }

            // Crear nuevo estado si es necesario
            if (isset($data['status']) && $data['status'] !== $budget->status()->latest()->first()->status) {
                BudgetStatus::create([
                    'budget_id' => $budget->id,
                    'user_id' => auth()->id(),
                    'status' => $data['status']
                ]);
            }

            DB::commit();
            Log::info('Presupuesto actualizado correctamente:', ['budget_id' => $budget->id]);

            return [
                'success' => true,
                'message' => 'Presupuesto actualizado correctamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar presupuesto:', [
                'budget_id' => $budget->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el presupuesto: ' . $e->getMessage()
            ];
        }
    }


    /**
     * Elimina un presupuesto.
     *
     * @param Budget $budget
     * @return void
     */
    public function delete(Budget $budget)
    {
        $budget->delete();
    }

    /**
     * Selecciona un cliente para el presupuesto.
     */
    public function selectClient(Request $request): array
    {
        session()->forget(['budget_cart', 'budget_client']); // Limpiar la sesión

        $client = Client::find($request->client_id);

        if (!$client) {
            return ['success' => false, 'message' => 'El cliente no existe.'];
        }

        session()->put('budget_client', [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
        ]);

        return ['success' => true, 'message' => 'Cliente seleccionado correctamente.'];
    }

    /**
     * Agrega un producto al presupuesto.
     */
    public function addProduct(Request $request, int $productId): array
    {
        $product = Product::find($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'El producto no existe.'];
        }

        $cart = session('budget_cart', []);
        $quantity = $request->input('quantity', 1);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                "id" => $product->id,
                "name" => $product->name,
                "price" => $product->price ?? $product->old_price,
                "quantity" => $quantity,
            ];
        }

        session(['budget_cart' => $cart]);
        $this->updateTotal();

        return ['success' => true, 'message' => 'Producto agregado al presupuesto con éxito!'];
    }

    /**
     * Actualiza la cantidad de un producto en el presupuesto.
     */
    public function updateProductQuantity(int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            return ['success' => false, 'message' => 'La cantidad debe ser al menos 1.'];
        }

        $cart = session('budget_cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            session(['budget_cart' => $cart]);
            $this->updateTotal();

            return ['success' => true, 'message' => 'Presupuesto actualizado con éxito!'];
        }

        return ['success' => false, 'message' => 'Producto no encontrado en el presupuesto.'];
    }


    /**
     * Elimina un producto del presupuesto.
     */
    public function removeItem(int $productId): array
    {
        $cart = session('budget_cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session(['budget_cart' => $cart]);
            $this->updateTotal();

            return ['success' => true, 'message' => 'Producto eliminado del presupuesto con éxito.'];
        }

        return ['success' => false, 'message' => 'Producto no encontrado en el presupuesto.'];
    }

    /**
     * Vacía el presupuesto.
     */
    public function clearBudget(): void
    {
        session()->forget(['budget_cart', 'budget_client', 'budget_total']);
    }

    /**
     * Guarda el presupuesto en la base de datos.
     */
    public function saveBudget(array $data): array
    {
        try {
            DB::beginTransaction();

            $budget = Budget::create([
                'client_id' => $data['client_id'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'price_list_id' => $data['price_list_id'] ?? null,
                'store_id' => $data['store_id'],
                'due_date' => $data['due_date'],
                'notes' => $data['notes'] ?? null,
                'discount_type' => $data['discount_type'] ?: null,
                'discount' => $data['discount_type'] ? ($data['discount'] ?? 0) : null,
                'is_blocked' => $data['is_blocked'] ?? false,
                'total' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $productId => $item) {
                $product = Product::findOrFail($productId);
                $price = $product->build_price;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;

                // Calcular el discount_price para el item
                $discountPrice = 0;
                if (!empty($item['discount_type']) && isset($item['discount'])) {
                    if ($item['discount_type'] === 'Percentage') {
                        $discountPrice = $item['discount']; // Guardamos el porcentaje directamente
                        $itemTotal = $subtotal * (1 - ($discountPrice / 100));
                    } else {
                        $discountPrice = $item['discount'];
                        $itemTotal = $subtotal - ($quantity * $discountPrice);
                    }
                } else {
                    $itemTotal = $subtotal;
                }

                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount_type' => $item['discount_type'] ?: null,
                    'discount_price' => $discountPrice
                ]);

                $total += $itemTotal;
            }

            // Aplicar descuento general
            if (!empty($data['discount_type']) && isset($data['discount'])) {
                if ($data['discount_type'] === 'Percentage') {
                    $total = $total * (1 - ($data['discount'] / 100));
                } else {
                    $total = $total - $data['discount'];
                }
            }

            $budget->update(['total' => $total]);

            // Crear el BudgetStatus
            BudgetStatus::create([
                'budget_id' => $budget->id,
                'user_id' => auth()->id(),
                'status' => $data['status']
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'Presupuesto guardado correctamente'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar el presupuesto: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return ['success' => false, 'message' => 'Error al guardar el presupuesto: ' . $e->getMessage()];
        }
    }

    /**
     * Actualiza el total del presupuesto en la sesión.
     */
    private function updateTotal(): void
    {
        $cart = session('budget_cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        session(['budget_total' => $total]);
        Log::debug('Total del presupuesto actualizado', ['total' => $total]);
    }

    /**
     * Obtiene todos los datos necesarios para el checkout.
     *
     * @param Budget $budget
     * @return array
     */
    public function getCheckoutData(Budget $budget): array
    {
        $budget->load([
            'client',
            'lead',
            'store',
            'items.product'
        ]);

        // Calcular subtotal
        $subtotal = $budget->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $budget->subtotal = $subtotal;

        // Calcular total con descuento
        if ($budget->discount_type === 'Percentage') {
            $budget->total = $subtotal * (1 - ($budget->discount / 100));
        } elseif ($budget->discount_type === 'Fixed') {
            $budget->total = $subtotal - $budget->discount;
        } else {
            $budget->total = $subtotal;
        }

        return compact('budget');
    }

    /**
     * Procesa el checkout y crea la orden.
     *
     * @param Budget $budget
     * @param array $data
     * @return array
     */
    public function processCheckout(Budget $budget, array $data): array
    {
        try {
            DB::beginTransaction();

            // Preparar el array de productos
            $products = [];
            $subtotal = 0;
            foreach ($budget->items as $item) {
                $basePrice = $item->price;
                $finalPrice = $basePrice;
                
                if ($item->discount_type === 'Percentage' && $item->discount_price > 0) {
                    $finalPrice = $basePrice * (1 - ($item->discount_price / 100));
                } elseif ($item->discount_type === 'Fixed' && $item->discount_price > 0) {
                    $finalPrice = $basePrice - $item->discount_price;
                }
    
                $itemTotal = $finalPrice * $item->quantity;
                $subtotal += $itemTotal;
    
                $products[] = [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $finalPrice,
                    'total' => $itemTotal
                ];
            }
    
            // Calcular total con descuento general
            $total = $subtotal;
            if ($budget->discount > 0) {
                if ($budget->discount_type === 'Percentage') {
                    $total = $subtotal * (1 - ($budget->discount / 100));
                } else {
                    $total = $subtotal - $budget->discount;
                }
            }
            
            // Crear la orden con todos los campos necesarios
            $order = Order::create([
                'client_id' => $budget->client_id,
                'lead_id' => $budget->lead_id,
                'store_id' => $budget->store_id,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
                'total' => $total,
                'subtotal' => $subtotal,
                'discount' => $budget->discount,
                'products' => json_encode($products),
                'notes' => $data['notes'] ?? null,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
                'origin' => 'physical',
                'tax' => 0,
                'shipping' => 0,
                'seller_id' => auth()->id(),
                'shipping_status' => 'delivered',
                'created_by' => auth()->id(),
                'uuid' => Str::uuid(),
                'cash_register_log_id' => $data['cash_register_log_id'] ?? null // Get it from the request data
            ]);

            // Actualizar el presupuesto
            $budget->order_id = $order->id;
            $budget->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Venta procesada correctamente',
                'redirect' => route('orders.show', $order->uuid) // Usar el UUID en la redirección
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar el checkout:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ];
        }
    }
}