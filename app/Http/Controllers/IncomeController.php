<?php

namespace App\Http\Controllers;

use App\Exceptions\CFEException;
use App\Exports\IncomeExport;
use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\UpdateIncomeRequest;
use App\Repositories\IncomeRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CashRegisterLog;

class IncomeController extends Controller
{
    /**
     * El repositorio para las operaciones de ingresos.
     *
     * @var IncomeRepository
     */
    protected $incomeRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param IncomeRepository $incomeRepository
     */
    public function __construct(IncomeRepository $incomeRepository)
    {
        $this->middleware(['check_permission:access_incomes', 'user_has_store'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_incomes'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->incomeRepository = $incomeRepository;
    }

    /**
     * Muestra una lista de todos los ingresos.
     *
     * @return View
     */
    public function index(): View|JsonResponse|RedirectResponse
    {
        // Buscar cash_register_log abierto del usuario actual
        $openCashRegisterLog = CashRegisterLog::whereHas('cashRegister', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereNull('close_time')
            ->first();

        if (!$openCashRegisterLog) {
            return redirect()
                ->route('points-of-sales.index')
                ->with('error', 'Debe abrir una caja registradora para acceder a los ingresos libres.');
        }

        $data = $this->incomeRepository->getAllIncomes($openCashRegisterLog->id);
        
        return view('content.accounting.incomes.income.index', $data);
    }

    /**
     * Muestra el formulario para crear un nuevo ingreso.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.incomes.create-income');
    }

    /**
     * Almacena un nuevo ingreso en la base de datos.
     *
     * @param StoreIncomeRequest $request
     * @return JsonResponse
     */
    public function store(StoreIncomeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $income = $this->incomeRepository->store($validated);
            return response()->json(['success' => true, 'data' => $income]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el ingreso.'], 400);
        }
    }

    /**
     * Muestra un ingreso específico.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View|JsonResponse
    {
        $income = $this->incomeRepository->getIncomeById($id);
        $income->load('store');

        return view('content.accounting.incomes.income.details-income', compact('income'));
    }

    /**
     * Devuelve los datos de un ingreso específico para edición.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $income = $this->incomeRepository->getIncomeById($id);
            return response()->json($income);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos del ingreso.'], 400);
        }
    }

    /**
     * Actualiza un ingreso específico.
     *
     * @param UpdateIncomeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateIncomeRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $income = $this->incomeRepository->update($id, $validated);
            return response()->json(['success' => true, 'data' => $income]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el ingreso.'], 400);
        }
    }

    /**
     * Elimina un ingreso específico.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->incomeRepository->destroyIncome($id);
            return response()->json(['success' => true, 'message' => 'Ingreso eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el ingreso.'], 400);
        }
    }

    /**
     * Elimina varios ingresos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->incomeRepository->deleteMultipleIncomes($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Ingresos eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los ingresos.'], 400);
        }
    }

    /**
     * Obtiene los ingresos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->incomeRepository->getIncomesForDataTable($request);
    }


    /**
     * Maneja la emisión de la factura (CFE).
     *
     * @param Request $request
     * @param int $incomeId
     * @return RedirectResponse
     */

     public function emitCFE(Request $request, int $incomeId): RedirectResponse
     {
         try {
             $this->incomeRepository->emitCFE($incomeId, $request);
             return redirect()->back()->with('success', 'Factura emitida correctamente.');
         } catch (CFEException $e) {
            Log::error("Error al emitir CFE para la orden {$incomeId}: {$e->getMessage()}");
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
             Log::error("Error al emitir CFE para la orden {$incomeId}: {$e->getMessage()}");
             return redirect()->back()->with('error', 'Error al emitir la factura. Por favor, intente nuevamente.');
         }
     }

    /**
     * Exporta los ingresos a Excel.
     *  
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportExcel(Request $request)
    {
        try {
            $entityType = $request->input('entity_type');
            $categoryId = $request->input('category_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $incomes = $this->incomeRepository->getIncomesForExport($entityType, $categoryId, $startDate, $endDate);
            return Excel::download(new IncomeExport($incomes), 'ingresos-' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los ingresos a Excel. Por favor, intente nuevamente.');
        }
    }

    /**
     * Exporta los ingresos a PDF.
     *
     * @param Request $request
     * @param int|null $incomeId
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request, ?int $incomeId = null)
    {
        try {
            // Si viene un ID específico, solo exportar ese ingreso
            if ($incomeId) {
                $income = $this->incomeRepository->getIncomeById($incomeId);
                $income->load(['client', 'supplier', 'paymentMethod', 'incomeCategory', 'taxRate']);

                // Calcular subtotal
                $subtotal = collect($income->items)->reduce(function ($carry, $item) {
                    return $carry + ($item['price'] * $item['quantity']);
                }, 0);

                // Calcular impuesto
                $taxAmount = 0;
                if ($income->tax_rate_id && $income->taxRate) {
                    $taxAmount = $subtotal * ($income->taxRate->rate / 100);
                }

                $totals = [
                    'subtotal' => $subtotal,
                    'tax' => $taxAmount,
                    'total' => $income->income_amount
                ];

                $incomes = collect([$income]);

                $pdf = Pdf::loadView('content.accounting.incomes.income.export-pdf', 
                    compact('incomes', 'totals')
                );

                return $pdf->download('ingreso-' . $incomeId . '-' . date('Y-m-d_H-i-s') . '.pdf');
            }

            // Si no viene ID, exportar todos los ingresos según los filtros
            $entityType = $request->input('entity_type');
            $categoryId = $request->input('category_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $incomes = $this->incomeRepository->getIncomesForExport($entityType, $categoryId, $startDate, $endDate);

            // Calcular totales globales
            $totals = [
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0
            ];

            foreach ($incomes as $income) {
                $subtotal = collect($income->items)->reduce(function ($carry, $item) {
                    return $carry + ($item['price'] * $item['quantity']);
                }, 0);

                $taxAmount = 0;
                if ($income->tax_rate_id && $income->taxRate) {
                    $taxAmount = $subtotal * ($income->taxRate->rate / 100);
                }

                $totals['subtotal'] += $subtotal;
                $totals['tax'] += $taxAmount;
                $totals['total'] += $income->income_amount;
            }

            $pdf = Pdf::loadView('content.accounting.incomes.income.export-pdf', 
                compact('incomes', 'totals')
            );

            return $pdf->download('ingresos-' . date('Y-m-d_H-i-s') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Error exportando PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar el PDF. Por favor, intente nuevamente.');
        }
    }

    /**
     * Exporta un CFE en formato PDF para un ingreso específico.
     *
     * @param Request $request
     * @param int $incomeId
     * @return \Illuminate\Http\Response
     */
    public function exportCfePdf(Request $request, int $incomeId)
    {
        try {
            return $this->incomeRepository->exportCfePdf($incomeId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Obtiene la última cotización del dolar.
     *
     * @return JsonResponse
     */
    public function getDollarRate(): JsonResponse
    {
        try {
            $rate = $this->incomeRepository->getDollarRate();
            return response()->json(['success' => true, 'rate' => $rate]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener la cotización.'], 400);
        }
    }
}