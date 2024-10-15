<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCashRegisterRequest;
use App\Http\Requests\UpdateCashRegisterRequest;
use Illuminate\View\View;
use App\Repositories\CashRegisterRepository;
use App\Repositories\CashRegisterLogRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use PDF;


class CashRegisterController extends Controller
{
    protected $cashRegisterRepository;
    protected $cashRegisterLogRepository;

    public function __construct(CashRegisterRepository $cashRegisterRepository, CashRegisterLogRepository $cashRegisterLogRepository)
    {
        $this->cashRegisterRepository = $cashRegisterRepository;
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
    }

    /**
     * Muestra una lista de todas las cajas registradoras.
     *
     */
    public function index()
    {
        $userId = auth()->user()->id;
        $openCashRegisterId = $this->cashRegisterLogRepository->hasOpenLogForUser($userId);

        if ($openCashRegisterId) {
            $storeId = $this->cashRegisterRepository->findStoreByCashRegisterId($openCashRegisterId);

            Session::put('open_cash_register_id', $openCashRegisterId);
            Session::put('store_id', $storeId);

           //return redirect()->route('pdv.front');
        } else {
            Session::forget('open_cash_register_id');
            Session::forget('store_id');
            $cajas = $this->cashRegisterRepository->getCashRegistersForDatatable($userId);
            //return view('points-of-sales.index', compact('cajas', 'userId'));
        }
        $cajas = $this->cashRegisterRepository->getCashRegistersForDatatable($userId);
        return view('points-of-sales.index', compact('cajas', 'userId'));
    }



    /**
     * Agrega una caja registradora a la base de datos.
     *
     * @param StoreCashRegisterRequest $request
     * @return JsonResponse
     */
    public function store(StoreCashRegisterRequest $request)
    {
        $validatedData = $request->validated();
        $cashRegister = $this->cashRegisterRepository->createCashRegister($validatedData);
        return response()->json($cashRegister, 201);
    }

    /**
     * Devuelve una caja registradora dado un id.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id)
    {
        $cashRegister = $this->cashRegisterRepository->getCashRegisterById($id);

        if ($cashRegister) {
            return response()->json($cashRegister);
        } else {
            return response()->json(['message' => 'Cash register not found.'], 404);
        }
    }

    /**
     * Actualiza una caja registradora ya creada.
     *
     * @param UpdateCashRegisterRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateCashRegisterRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $updated = $this->cashRegisterRepository->updateCashRegister($id, $validatedData);

        if ($updated) {
            return response()->json(['message' => 'Caja registradora actualizada correctamente.']);
        } else {
            return response()->json(['message' => 'Ha ocurrido un error al intentar actualizar la caja registradora.'], 404);
        }
    }

    /**
     * Borra una caja registradora dado un id.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        $deleted = $this->cashRegisterRepository->deleteCashRegister($id);

        if ($deleted) {
            return response()->json(['message' => 'Caja registradora borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar la caja registradora que se deseó borrar.'], 404);
        }
    }

    /**
     * Devuelve la(s) empresa(s) a las cuales le puede abrir una caha registradora.
     *
     * @return JsonResponse
     */
    public function storesForCashRegister()
    {
        $stores = $this->cashRegisterRepository->storesForCashRegister();
        return response()->json($stores, 201);
    }

    /**
     * Devuelve los balances y ventas de la caja registradora.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function getDetails(string $id){

        if (!Auth::user()->hasRole('Administrador')) {
            abort(403, 'No tienes permiso para ver los logs de la caja registradora.');
        }

        $details = $this->cashRegisterRepository->getDetails($id);
        $cashRegister = $this->cashRegisterRepository->getCashRegisterById($id);
        $openCount = $details->whereNull('close_time')->count();
        $closedCount = $details->whereNotNull('close_time')->count();

        return view('points-of-sales.details', compact('cashRegister','details','openCount','closedCount'));
    }

    /**
     * Devuelve las ventas realizadas por una caja registradora.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getSales($id){
        if (!Auth::user()->hasRole('Administrador')) {
            abort(403, 'No tienes permiso para ver las ventas de la caja registradora.');
        }
        $sales = $this->cashRegisterRepository->getSales($id);
        $totalSales = $sales->count();
        $cashSales = $sales->sum('cash_sales');
        $posSales = $sales->sum('pos_sales');

        return view('points-of-sales.sales', compact('sales', 'totalSales', 'cashSales', 'posSales','id'));
    }

    /**
     * Devuelve las ventas realizadas por una caja registradora.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getSalesPdf($id){
        if (!Auth::user()->hasRole('Administrador')) {
            abort(403, 'No tienes permiso para ver las ventas de la caja registradora.');
        }
        $sales = $this->cashRegisterRepository->getSales($id);
        $pdf = PDF::loadView('points-of-sales.exportSales', compact('sales','id'));

        return $pdf->stream('cash_register_sales.pdf');
    }
}
