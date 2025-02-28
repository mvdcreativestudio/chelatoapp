<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\BudgetRepository;
use Illuminate\Http\RedirectResponse;
use App\Models\Budget;
use App\Models\Product;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Store;
use App\Models\PriceList;
use App\Models\BudgetItem;
use App\Models\BudgetStatus;
use App\Models\Order;
use App\Http\Requests\UpdateBudgetRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\Helpers;
use App\Http\Controllers\AccountingController;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    /**
     * El repositorio de Presupuestos.
     *
     * @var BudgetRepository
     */
    protected BudgetRepository $budgetRepository;

    /**
     * Constructor para inyectar el repositorio.
     *
     * @param BudgetRepository $budgetRepository
     */
    public function __construct(BudgetRepository $budgetRepository)
    {
        $this->budgetRepository = $budgetRepository;
    }

    /**
     * Muestra la vista del presupuesto con los productos y clientes disponibles.
     */
    public function index(Request $request)
    {
        $budgets = Budget::with([
            'client',
            'lead',
            'store',
            'items.product',
            'latestStatus'
        ])->get();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $budgets
            ]);
        }

        return view('budgets.index', compact('budgets'));
    }

    /**
     * Selecciona un cliente y lo almacena en la sesión.
     */
    public function selectClient(Request $request): RedirectResponse
    {
        $result = $this->budgetRepository->selectClient($request);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Agrega un producto al presupuesto.
     */
    public function addToBudget(Request $request, int $productId): RedirectResponse
    {
        $result = $this->budgetRepository->addProduct($request, $productId);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Actualiza la cantidad de un producto en el presupuesto.
     */
    public function updateBudgetItem(Request $request): RedirectResponse
    {
        $result = $this->budgetRepository->updateProductQuantity($request->id, $request->quantity);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Elimina un producto del presupuesto.
     */
    public function removeFromBudget(Request $request): RedirectResponse
    {
        $result = $this->budgetRepository->removeItem($request->id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Vacía el presupuesto.
     */
    public function clearBudget(): RedirectResponse
    {
        $this->budgetRepository->clearBudget();
        return redirect()->back()->with('success', 'Presupuesto vaciado con éxito.');
    }

    /**
     * Guarda el presupuesto en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Validar datos
            $validatedData = $request->validate([
                'client_id' => 'nullable|exists:clients,id|required_without:lead_id',
                'lead_id' => 'nullable|exists:leads,id|required_without:client_id',
                'price_list_id' => 'nullable|exists:price_lists,id',
                'store_id' => 'required|exists:stores,id',
                'due_date' => 'required|date',
                'notes' => 'nullable|string',
                'discount_type' => 'nullable|in:Percentage,Fixed',
                'discount' => 'nullable|numeric',
                'status' => 'required|in:draft,pending_approval,sent,negotiation,approved,rejected,expired,cancelled',
                'is_blocked' => 'boolean',
                'products' => 'required|array',
                'products.*' => 'exists:products,id',
            ]);

            // Agregar logging para debug
            Log::info('Datos recibidos:', $request->all());
            
            // Preparar datos para el repositorio
            $data = $validatedData;
            $data['items'] = [];
            
            // Procesar los productos seleccionados
            foreach ($request->input('products', []) as $productId) {
                $data['items'][$productId] = [
                    'quantity' => $request->input("items.{$productId}.quantity", 1),
                    'discount_type' => $request->input("items.{$productId}.discount_type"),
                    'discount' => $request->input("items.{$productId}.discount"),
                ];
            }

            $result = $this->budgetRepository->saveBudget($data);

            if ($result['success']) {
                return redirect()
                    ->route('budgets.index')
                    ->with('success', $result['message']);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result['message']);
        } catch (\Exception $e) {
            Log::error('Error al guardar el presupuesto: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al guardar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene la lista de productos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProducts()
    {
        $products = Product::all();
        return response()->json(['products' => $products]);
    }

    /**
     * Obtiene la lista de clientes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClients()
    {
        $clients = Client::all();
        return response()->json(['clients' => $clients]);
    }

    /**
     * Muestra la vista para crear un nuevo presupuesto.
     */
    public function create()
    {
        $clients = Client::all();
        $leads = Lead::all();
        $priceLists = PriceList::all();
        $stores = Store::all();
        $userId = Auth::id();
        $products = Product::all();// product where store id
        return view('budgets.create', compact('clients', 'leads', 'priceLists', 'stores', 'products'));
    }

    public function destroy(Budget $budget)
    {
        try {
            DB::beginTransaction();
            
            // Eliminar registros relacionados
            $budget->items()->delete();
            $budget->status()->delete();
            $budget->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Presupuesto eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar el presupuesto: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el presupuesto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra los detalles de un presupuesto específico.
     *
     * @param Budget $budget
     * @return \Illuminate\View\View
     */
    public function detail(Budget $budget)
    {
        // Cargar las relaciones necesarias
        $budget->load([
            'client',
            'lead',
            'store',
            'items.product',
            'status'
        ]);

        $companySettings = \App\Models\CompanySettings::first(); // Add this line

        // Calcular subtotal teniendo en cuenta los descuentos por producto
        $subtotal = $budget->items->sum(function ($item) {
            $itemTotal = $item->quantity * $item->price;
            
            // Aplicar descuento específico del producto si existe
            if ($item->discount_type === 'Percentage' && $item->discount_price > 0) {
                $itemTotal = $itemTotal * (1 - ($item->discount_price / 100));
            } elseif ($item->discount_type === 'Fixed' && $item->discount_price > 0) {
                $itemTotal = $itemTotal - ($item->quantity * $item->discount_price);
            }
            
            return $itemTotal;
        });

        $budget->subtotal = $subtotal;

        // Calcular el total con descuento general si existe
        if ($budget->discount_type === 'Percentage') {
            $budget->total = $subtotal * (1 - ($budget->discount / 100));
        } elseif ($budget->discount_type === 'Fixed') {
            $budget->total = $subtotal - $budget->discount;
        } else {
            $budget->total = $subtotal;
        }

        // Calcular el total por item para mostrar en la tabla
        foreach ($budget->items as $item) {
            $itemTotal = $item->quantity * $item->price;
            
            if ($item->discount_type === 'Percentage' && $item->discount_price > 0) {
                $item->total = $itemTotal * (1 - ($item->discount_price / 100));
            } elseif ($item->discount_type === 'Fixed' && $item->discount_price > 0) {
                $item->total = $itemTotal - ($item->quantity * $item->discount_price);
            } else {
                $item->total = $itemTotal;
            }
        }

        $statusTranslations = [
            'draft' => 'Borrador',
            'pending_approval' => 'Pendiente de Aprobación',
            'sent' => 'Enviado',
            'negotiation' => 'En Negociación',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado'
        ];

        return view('budgets.detail', compact('budget', 'statusTranslations', 'companySettings'));
    }

    public function updateStatus(Budget $budget, Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Crear nuevo estado
            BudgetStatus::create([
                'budget_id' => $budget->id,
                'user_id' => auth()->id(),
                'status' => $request->status
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar estado: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ], 500);
        }
    }

    public function convertToOrder(Budget $budget)
    {
        try {
            if ($budget->status()->latest()->first()->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este presupuesto está en estado "Rechazado" y no puede ser convertido a venta.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'redirect' => route('budgets.checkout', $budget->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al convertir presupuesto a orden:', [
                'budget_id' => $budget->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud'
            ], 500);
        }
    }

    public function checkout(Budget $budget)
    {
        // Cargar las relaciones necesarias
        $budget->load(['client', 'lead', 'store', 'items.product']);

        // Calcular subtotal considerando descuentos por producto
        $subtotal = $budget->items->sum(function ($item) {
            $basePrice = $item->price;
            $finalPrice = $basePrice;
            
            // Aplicar descuento específico del producto
            if ($item->discount_type === 'Percentage' && $item->discount_price > 0) {
                $finalPrice = $basePrice * (1 - ($item->discount_price / 100));
            } elseif ($item->discount_type === 'Fixed' && $item->discount_price > 0) {
                $finalPrice = $basePrice - $item->discount_price;
            }
            
            return $finalPrice * $item->quantity;
        });

        // Calcular total aplicando el descuento general
        $total = $subtotal;
        if ($budget->discount > 0) {
            if ($budget->discount_type === 'Percentage') {
                $total = $subtotal * (1 - ($budget->discount / 100));
            } else { // Fixed
                $total = $subtotal - $budget->discount;
            }
        }

        $budget->subtotal = $subtotal;
        $budget->total = $total;

        return view('budgets.checkout', compact('budget'));
    }

    public function processCheckout(Budget $budget, Request $request)
    {
        $result = $this->budgetRepository->processCheckout($budget, $request->all());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => $result['redirect'] // Usar la URL que ya viene en el resultado
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    public function edit(Budget $budget)
    {
        $budget->load(['client', 'lead', 'store', 'items.product', 'status']);
        $clients = Client::all();
        $leads = Lead::all();
        $priceLists = PriceList::all();
        $stores = Store::all();
        $products = Product::all();
        $currentStatus = optional($budget->status()->latest()->first())->status ?? 'draft';

        return view('budgets.edit', compact('budget', 'clients', 'leads', 'priceLists', 'stores', 'products', 'currentStatus'));
    }

    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        try {
            Log::info('Iniciando actualización en controller:', [
                'budget_id' => $budget->id,
                'request_data' => $request->all()
            ]);

            $validatedData = $request->validated();
            Log::info('Datos validados:', $validatedData);

            $result = $this->budgetRepository->update($budget, $validatedData);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'redirect' => route('budgets.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BudgetController@update:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el presupuesto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generatePdf(Request $request, Budget $budget)
    {
        $budget->load(['client', 'lead', 'store', 'items.product']);
        $companySettings = \App\Models\CompanySettings::first();
    
        // Calcular totales igual que en detail()
        $subtotal = $budget->items->sum(function ($item) {
            $itemTotal = $item->quantity * $item->price;
            if ($item->discount_type === 'Percentage' && $item->discount_price > 0) {
                $itemTotal = $itemTotal * (1 - ($item->discount_price / 100));
            } elseif ($item->discount_type === 'Fixed' && $item->discount_price > 0) {
                $itemTotal = $itemTotal - ($item->quantity * $item->discount_price);
            }
            return $itemTotal;
        });
    
        $budget->subtotal = $subtotal;
        $budget->total = $subtotal;
    
        if ($budget->discount_type === 'Percentage') {
            $budget->total *= (1 - ($budget->discount / 100));
        } elseif ($budget->discount_type === 'Fixed') {
            $budget->total -= $budget->discount;
        }
    
        foreach ($budget->items as $item) {
            $itemTotal = $item->quantity * $item->price;
            if ($item->discount_type === 'Percentage' && $item->discount_price > 0) {
                $item->total = $itemTotal * (1 - ($item->discount_price / 100));
            } elseif ($item->discount_type === 'Fixed' && $item->discount_price > 0) {
                $item->total = $itemTotal - ($item->quantity * $item->discount_price);
            } else {
                $item->total = $itemTotal;
            }
        }
    
        $pdf = PDF::loadView('budgets.pdf', compact('budget', 'companySettings'));
    
        if ($request->query('action') === 'print') {
            return $pdf->stream('budget-' . $budget->id . '.pdf');
        } else {
            return $pdf->download('budget-' . $budget->id . '.pdf');
        }
    }

}