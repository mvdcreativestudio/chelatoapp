<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PosOrderRepository;
use Illuminate\View\View;
use App\Http\Requests\StorePosOrderRequest;
use App\Http\Requests\UpdatePosOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PosOrderController extends Controller
{
    protected $posOrderRepo;

    public function __construct(PosOrderRepository $posOrderRepo)
    {
        $this->posOrderRepo = $posOrderRepo;
    }

    /**
     * Muestra una lista de todas las ordenes realizadas en la caja registradora.
     *
     * @return View
     */
    public function index(): View
    {
        $userId = auth()->user()->id;
        $posOrders = $this->posOrderRepo->getPosOrdersForDatatable($userId);
        return view('pos-orders.index', compact('posOrders', 'userId'));
    }

    /**
     * Devuelve una orden realizada en la caja registradora dado un id.
     *
     * @param string $id
     * @param JsonResponse
     *
     */
    public function show($id)
    {
        $posOrder = $this->posOrderRepo->findById($id);
        if ($posOrder) {
            return response()->json($posOrder);
        }
        return response()->json(['error' => 'No se encontró la orden con el id asociado.'], 404);
    }

    /**
     * Agrega una orden realizada en la caja registradora a la base de datos.
     *
     * @param StorePosOrderRequest $request
     * @return JsonResponse
     */
    public function store(StorePosOrderRequest $request)
    {
        $validatedData = $request->validated();

        Log::info('Validated data: ' . json_encode($validatedData));

        // Convertir los productos a un array
        $products = json_decode($validatedData['products'], true);

        Log::info('Products controller: ' . json_encode($products));

        // Intentar actualizar el stock solo si el producto tiene un stock definido
        $stockUpdated = $this->posOrderRepo->updateProductStock($products);

        if (!$stockUpdated) {
            // Si el stock no se actualizó, retornar un error
            return response()->json(['error' => 'Stock insuficiente en uno o más productos'], 400);
        }

        // Crear la orden después de actualizar el stock
        $order = $this->posOrderRepo->create($validatedData);

        return response()->json($order, 201);
    }


    /**
     * Actualiza una caja registradora ya creada.
     *
     * @param UpdatePosOrderRequest $request
     * @param string $id
     * @return JsonResponse
     *
     */
    public function update(UpdatePosOrderRequest $request, $id)
    {
        $validatedData = $request->validated();
        $updated = $this->posOrderRepo->update($id, $validatedData);

        if ($updated) {
            return response()->json(['message' => 'Orden actualizada correctamente.']);
        } else {
            return response()->json(['message' => 'Ha ocurrido un error al intentar actualizar la orden.'], 404);
        }
    }

    /**
     * Borra una orden realizada en la caja registradora dado un id.
     *
     * @param string $id
     */
    public function destroy($id)
    {
        $deleted = $this->posOrderRepo->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Orden eliminada correctamente.'], 200);
        }
        return response()->json(['error' => 'Ha ocurrido un error al intentar eliminar la orden.'], 404);
    }

    /**
     * Calcula el precio después de aplicar un descuento porcentual.
     *
     * @param int $price
     * @param int $percentage
     * @return JsonResponse
     */
    public function percentageDiscount($price, $percentage)
    {
        $newPrice = $this->posOrderRepo->percentageDiscount($price, $percentage);
        return response()->json(['message' => 'Descuento aplicado correctamente.', 'newPrice' => $newPrice], 200);
    }

    /**
     * Calcula el precio después de aplicar un descuento fijo.
     *
     * @param int $price
     * @param int $discount
     * @return JsonResponse
     */
    public function fixedDiscount($price, $discount)
    {
        $newPrice = $this->posOrderRepo->fixedDiscount($price, $discount);
        return response()->json(['message' => 'Descuento aplicado correctamente.', 'newPrice' => $newPrice], 200);
    }

    /**
     * Calcula el vuelto a devolver.
     *
     * @param int $total
     * @param int $money
     * @return JsonResponse
     */
    public function calculateDifference($total, $money)
    {
        $difference = $this->posOrderRepo->calculateDifference($total, $money);
        return response()->json(['message' => 'Vuelto calculado correctamente.', 'difference' => $difference], 200);
    }
}
