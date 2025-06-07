<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderItemRequest;
use App\Http\Requests\UpdatePurchaseOrderItemRequest;
use App\Repositories\PurchaseOrderItemRepository;
use Illuminate\Http\Request;

class PurchaseOrderItemController extends Controller
{
    protected $purchaseOrderItemRepository;

    public function __construct(PurchaseOrderItemRepository $purchaseOrderItemRepository)
    {
        $this->purchaseOrderItemRepository = $purchaseOrderItemRepository;
    }

    public function index(Request $request)
    {
        $id = session('purchase_order_id'); 
        $purchaseOrdersItems = $this->purchaseOrderItemRepository->getAll($id);
        return view('purchase-orders-items.index', compact('purchaseOrdersItems','id'));
    }

    public function storePurchasedItemId(Request $request)
    {
        $id = $request->input('id');
        session(['purchasedItemId' => $id]);
        return response()->json(['success' => true]);
    }

    public function getRawMaterials()
    {
        try {
            $rawMaterials = $this->purchaseOrderItemRepository->getRawMaterials();
            return response()->json($rawMaterials);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function getProducts()
    {
        try {
            $products = $this->purchaseOrderItemRepository->getProducts();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }


    public function storePurchaseOrderId(Request $request)
    {
        $id = $request->input('id');
        session(['purchase_order_id' => $id]); 
        return response()->json(['success' => true]); 
    }



    public function create()
    {
        return view('purchase_orders.create');
    }

    public function store(StorePurchaseOrderItemRequest $request)
    {
        $id = session('purchase_order_id'); 
        $request->merge(['purchase_orders_id' => $id]);
        $purchaseOrderItem = $this->purchaseOrderItemRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ítem de orden de compra creado con éxito.',
                'purchaseOrderItem' => $purchaseOrderItem
            ]);
        }

        return redirect()->route('purchase-orders-items.index')->with('success', 'Ítem de orden de compra creado con éxito.');
    }

    public function show($id)
    {
        $purchaseOrderItem = $this->purchaseOrderItemRepository->find($id);
        return view('purchase-orders-items.show', compact('purchaseOrderItem'));
    }

    public function edit($id)
    {
        $purchaseOrderItem = $this->purchaseOrderItemRepository->find($id);
        return view('purchase-orders-items.edit', compact('purchaseOrderItem'));
    }

    public function update(UpdatePurchaseOrderItemRequest $request, $id)
    {
        $this->purchaseOrderItemRepository->update($id, $request->validated());
        return redirect()->route('purchase-orders-items.index')->with('success', 'Ítem de orden de compra actualizado con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->purchaseOrderItemRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Item de orden de compra borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el item de la orden de compra que se deseó borrar.'], 404);
        }    
    }
}
