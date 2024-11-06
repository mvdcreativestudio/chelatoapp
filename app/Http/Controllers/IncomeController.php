<?php

namespace App\Http\Controllers;

use App\Exports\IncomeExport;
use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\UpdateIncomeRequest;
use App\Repositories\IncomeRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

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
    public function index(): View
    {
        $incomes = $this->incomeRepository->getAllIncomes();
        // dd($incomes);
        return view('content.accounting.incomes.income.index', $incomes);
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
    public function show(int $id): View
    {
        $income = $this->incomeRepository->getIncomeById($id);
        return view('content.accounting.incomes.details-income', compact('income'));
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
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los ingresos a Excel. Por favor, intente nuevamente.');
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $entityType = $request->input('entity_type');
            $categoryId = $request->input('category_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $incomes = $this->incomeRepository->getIncomesForExport($entityType, $categoryId, $startDate, $endDate);

            $pdf = Pdf::loadView('content.accounting.incomes.income.export-pdf', compact('incomes'));
            return $pdf->download('ingresos-' . date('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los ingresos a PDF. Por favor, intente nuevamente.');
        }
    }
}
