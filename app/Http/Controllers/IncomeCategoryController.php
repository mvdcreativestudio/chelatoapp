<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomeCategoryRequest;
use App\Http\Requests\UpdateIncomeCategoryRequest;
use App\Repositories\IncomeCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class IncomeCategoryController extends Controller
{
    /**
     * El repositorio para las operaciones de categorías de ingresos.
     *
     * @var IncomeCategoryRepository
     */
    protected $incomeCategoryRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param IncomeCategoryRepository $incomeCategoryRepository
     */
    public function __construct(IncomeCategoryRepository $incomeCategoryRepository)
    {
        $this->middleware(['check_permission:access_income-categories'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_income-categories'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->incomeCategoryRepository = $incomeCategoryRepository;
    }

    /**
     * Muestra una lista de todas las categorías de ingresos.
     *
     * @return View
     */
    public function index(): View
    {
        $incomeCategories = $this->incomeCategoryRepository->getAllIncomeCategories();
        return view('content.accounting.incomes.incomes-categories.index', $incomeCategories);
    }

    /**
     * Muestra el formulario para crear una nueva categoría de ingresos.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.income_categories.create');
    }

    /**
     * Almacena una nueva categoría de ingresos en la base de datos.
     *
     * @param StoreIncomeCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreIncomeCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $incomeCategory = $this->incomeCategoryRepository->store($validated);
            return response()->json(['success' => true, 'data' => $incomeCategory]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar la categoría de ingreso.'], 400);
        }
    }

    /**
     * Muestra una categoría de ingreso específica.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $incomeCategory = $this->incomeCategoryRepository->getIncomeCategoryById($id);
        return view('content.accounting.income_categories.details', compact('incomeCategory'));
    }

    /**
     * Devuelve los datos de una categoría de ingreso específica para edición.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $incomeCategory = $this->incomeCategoryRepository->getIncomeCategoryById($id);
            return response()->json($incomeCategory);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos de la categoría de ingreso.'], 400);
        }
    }

    /**
     * Actualiza una categoría de ingreso específica.
     *
     * @param UpdateIncomeCategoryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateIncomeCategoryRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $incomeCategory = $this->incomeCategoryRepository->update($id, $validated);
            return response()->json(['success' => true, 'data' => $incomeCategory]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar la categoría de ingreso.'], 400);
        }
    }

    /**
     * Elimina una categoría de ingreso específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->incomeCategoryRepository->destroyIncomeCategory($id);
            return response()->json(['success' => true, 'message' => 'Categoría de ingreso eliminada correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la categoría de ingreso.'], 400);
        }
    }

    /**
     * Elimina varias categorías de ingreso.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->incomeCategoryRepository->deleteMultipleIncomeCategories($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Categorías de ingresos eliminadas correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar las categorías de ingreso.'], 400);
        }
    }

    /**
     * Obtiene las categorías de ingresos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->incomeCategoryRepository->getIncomeCategoriesForDataTable($request);
    }
}
