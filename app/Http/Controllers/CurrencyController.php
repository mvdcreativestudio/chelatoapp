<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Repositories\CurrencyRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    /**
     * El repositorio para las operaciones de monedas.
     *
     * @var CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->middleware(['check_permission:access_currencies'])->only(
            [
                'index',
                'create',
                'show',
                'datatable'
            ]
        );

        $this->middleware(['check_permission:access_delete_currencies'])->only(
            [
                'destroy',
                'deleteMultiple'
            ]
        );

        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Muestra una lista de todas las monedas.
     *
     * @return View
     */
    public function index(): View
    {
        $currencies = $this->currencyRepository->getAllCurrencies();
        return view('content.accounting.currencies.index', $currencies);
    }

    /**
     * Muestra el formulario para crear una nueva moneda.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.currencies.add-currency');
    }

    /**
     * Almacena una nueva moneda en la base de datos.
     *
     * @param StoreCurrencyRequest $request
     * @return JsonResponse
     */
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        try {
            $currency = $this->currencyRepository->store($request->validated());
            return response()->json($currency);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar la moneda.'], 400);
        }
    }

    /**
     * Devuelve datos para una moneda específica.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $currency = $this->currencyRepository->getCurrencyById($id);
            return response()->json($currency);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos de la moneda.'], 400);
        }
    }

    /**
     * Actualiza una moneda específica.
     *
     * @param UpdateCurrencyRequest $request
     * @param Currency $currency
     * @return JsonResponse
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency): JsonResponse
    {
        try {
            $currency = $this->currencyRepository->update($currency, $request->validated());
            return response()->json($currency);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar la moneda.'], 400);
        }
    }

    /**
     * Eliminar una moneda específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->currencyRepository->destroyCurrency($id);
            return response()->json(['success' => true, 'message' => 'Moneda eliminada correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la moneda.'], 400);
        }
    }

    /**
     * Elimina varias monedas.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->currencyRepository->deleteMultipleCurrencies($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Monedas eliminadas correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar las monedas.'], 400);
        }
    }

    /**
     * Obtiene las monedas para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->currencyRepository->getCurrenciesForDataTable();
    }
}
