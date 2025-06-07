<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCashRegisterRequest;
use App\Http\Requests\UpdateCashRegisterRequest;
use App\Http\Requests\UpdateStoreCashRegisterPostMercadoPagoRequest;
use Illuminate\View\View;
use App\Repositories\CashRegisterRepository;
use App\Repositories\CashRegisterLogRepository;
use App\Repositories\MercadoPagoAccountPOSRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\CashRegister;
use App\Models\PosDevice;
use PDF;


class CashRegisterController extends Controller
{
    protected $cashRegisterRepository;
    protected $cashRegisterLogRepository;
    protected $mercadoPagoAccountPOSRepository;

    public function __construct(CashRegisterRepository $cashRegisterRepository, CashRegisterLogRepository $cashRegisterLogRepository, MercadoPagoAccountPOSRepository $mercadoPagoAccountPOSRepository)
    {
        $this->cashRegisterRepository = $cashRegisterRepository;
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
        $this->mercadoPagoAccountPOSRepository = $mercadoPagoAccountPOSRepository;
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
        }

        // Obtener las cajas registradoras
        $cajas = $this->cashRegisterRepository->getCashRegistersForDatatable($userId);

        // Obtener los dispositivos POS disponibles por store y pos_provider
        $posDevices = PosDevice::whereIn('pos_provider_id', function ($query) use ($cajas) {
            $query->select('pos_provider_id')
                ->from('stores')
                ->whereIn('id', $cajas->pluck('store_id'));
        })->get();

        // Retornar la vista con las cajas y los dispositivos POS
        return view('points-of-sales.index', compact('cajas', 'userId', 'posDevices'));
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


    public function getPosDevices(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id', // Validar que el store_id exista
        ]);

        // Filtrar los dispositivos POS según el store_id
        $posDevices = PosDevice::where('pos_provider_id', function ($query) use ($request) {
            $query->select('pos_provider_id')
                ->from('stores')
                ->where('id', $request->store_id); // Buscar el pos_integration_id de la tienda
        })->get();

        return response()->json($posDevices); // Devolver los dispositivos filtrados
    }



    public function linkPos(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'pos_device_id' => 'required|exists:pos_devices,id',
        ]);

        // Vincular el dispositivo POS a la caja registradora
        $cashRegister->posDevices()->syncWithoutDetaching([$request->pos_device_id]);

        return response()->json(['message' => 'POS vinculado correctamente.']);
    }


    public function unlinkPos(CashRegister $cashRegister, PosDevice $posDevice)
    {
        // Elimina el dispositivo POS específico de la caja registradora
        $cashRegister->posDevices()->detach($posDevice->id);

        return response()->json(['message' => 'POS desvinculado correctamente.']);
    }


    /**
     * Devuelve el POS de Mercado Pago.
     * 
     * @param $id
     *  @return JsonResponse
     */

    public function getPosMercadoPago($id){
        try {
            $mercadoPagoPos = $this->mercadoPagoAccountPOSRepository->getPOSByCashRegisterId($id);
            return response()->json([
                'success' => true,
                'message' => 'POS de Mercado Pago obtenido con éxito.',
                'mercadopago_pos' => $mercadoPagoPos
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se pudo obtener el POS de Mercado Pago.'], 404);
        }
    }

    /**
     * Guardar o editar el POS de Mercado Pago.
     * 
     * @param Request $request
     * @return JsonResponse
     */

    public function updatePosMercadoPago(UpdateStoreCashRegisterPostMercadoPagoRequest $request, $id){
        $data = $request->all();
        $data['cash_register_id'] = $id;
        $mercadoPagoPos = $this->mercadoPagoAccountPOSRepository->getPOSByCashRegisterId($id);
        if ($mercadoPagoPos) {
            try {
                $this->mercadoPagoAccountPOSRepository->update($mercadoPagoPos, $data);
                return response()->json([
                    'success' => true,
                    'message' => 'POS de Mercado Pago actualizado con éxito.'
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => 'No se pudo actualizar el POS de Mercado Pago.'], 404);
            }
        } else {
            try {
                $this->mercadoPagoAccountPOSRepository->store($data);
                return response()->json([
                    'success' => true,
                    'message' => 'POS de Mercado Pago creado con éxito.'
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => 'No se pudo crear el POS de Mercado Pago.'], 404);
            }
        }
    }


    /**
     * Elimina un POS de Mercado Pago.
     * 
     * @param $id
     * @return JsonResponse
     */

    public function deletePosMercadoPago($id){
        try {
            $mercadoPagoPos = $this->mercadoPagoAccountPOSRepository->getPOSByCashRegisterId($id);
            if ($mercadoPagoPos) {
                $this->mercadoPagoAccountPOSRepository->destroy($mercadoPagoPos->cash_register_id);
                return response()->json([
                    'success' => true,
                    'message' => 'POS de Mercado Pago eliminado con éxito.'
                ]);
            } else {
                return response()->json(['message' => 'No se pudo encontrar el POS de Mercado Pago para eliminar.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se pudo eliminar el POS de Mercado Pago.'], 404);
        }
    }
}
