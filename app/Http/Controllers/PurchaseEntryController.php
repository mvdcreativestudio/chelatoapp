<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseEntryRequest;
use App\Http\Requests\UpdatePurchaseEntryRequest;
use App\Repositories\BatchRepository;
use App\Repositories\PurchaseEntryRepository;
use App\Repositories\PurchaseOrderItemRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class PurchaseEntryController extends Controller
{
    protected $purchaseEntryRepository;
    protected $purchaseOrderItemRepository;
    protected $batchRepository;


    public function __construct(PurchaseEntryRepository $purchaseEntryRepository, PurchaseOrderItemRepository $purchaseOrderItemRepository, BatchRepository $batchRepository)
    {
        $this->purchaseEntryRepository = $purchaseEntryRepository;
        $this->purchaseOrderItemRepository = $purchaseOrderItemRepository;
        $this->batchRepository = $batchRepository;

    }

    public function index()
    {
        $id = session('purchase_order_id');
        $purchaseOrderItems = $this->purchaseOrderItemRepository->getAll($id);

        $purchaseOrderItemIds = $purchaseOrderItems->pluck('id')->toArray();
        $purchaseEntries = $this->purchaseEntryRepository->getAllByPurchaseOrderItems($purchaseOrderItemIds);
        $purchaseEntriesSum = $purchaseEntries->groupBy('purchase_order_items_id')
            ->mapWithKeys(function ($entries, $itemId) {
                return [$itemId => $entries->sum('quantity')];
            });
        
        return view('purchase-entries.index', compact('purchaseOrderItems','purchaseEntries', 'purchaseEntriesSum'));
    }

    

    public function create()
    {
        return view('purchase_entries.create');
    }

    public function store(StorePurchaseEntryRequest $request)
    {
        $data = $request->validated();

        // Obtenemos el item correspondiente
        $purchaseOrderItem = $this->purchaseOrderItemRepository->find($data['purchase_order_items_id']);

        // Calculamos la cantidad restante
        $totalReceived = $purchaseOrderItem->purchaseEntries ? $purchaseOrderItem->purchaseEntries->sum('received_quantity') : 0;
        $remainingQuantity = $purchaseOrderItem->quantity - $totalReceived;

        // Validamos que la cantidad recibida no sea mayor a la cantidad restante
        if ($data['received_quantity'] > $remainingQuantity) {
            return back()->withErrors(['received_quantity' => 'La cantidad recibida no puede ser mayor a la cantidad restante.']);
        }

        // Creamos la entrada de compra (purchase_entry)
        $purchaseEntry = $this->purchaseEntryRepository->create($data);


        // Retornamos la respuesta
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Entrada de compra creada con éxito.',
                'entry' => $purchaseEntry
            ]);
        }

        return redirect()->route('purchase-entries.index')->with('success', 'Entrada de compra creada con éxito.');
    }

    public function storeMultiple(Request $request)
    {
        $entries = $request->input('purchase_entries');

        foreach ($entries as $entry) {
            // Obtener el item correspondiente
            $purchaseOrderItem = $this->purchaseOrderItemRepository->find($entry['purchase_order_items_id']);

            // Usar la relación para obtener las entradas asociadas y calcular el total recibido
            $totalReceived = $purchaseOrderItem->purchaseEntries()->sum('quantity'); // Cambiado a 'quantity'
            $remainingQuantity = $purchaseOrderItem->quantity - $totalReceived;

            // Validar que la cantidad recibida no sea mayor a la cantidad restante
            if ($entry['received_quantity'] > $remainingQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cantidad recibida no puede ser mayor a la cantidad restante para el item con ID: ' . $purchaseOrderItem->id 
                ]);
            }

            // Crear la nueva entrada de compra
            $this->purchaseEntryRepository->create([
                'purchase_order_items_id' => $purchaseOrderItem->id,
                'quantity' => $entry['received_quantity'], // Cambiado a 'quantity'
                'entry_date' => $entry['entry_date'],
            ]);
        }

        return response()->json(['success' => true]);
    }


    public function show($id)
    {
        $purchaseEntry = $this->purchaseEntryRepository->find($id);
        return view('purchase_entries.show', compact('purchaseEntry'));
    }

    public function edit($id)
    {
        $purchaseEntry = $this->purchaseEntryRepository->find($id);
        return view('purchase_entries.edit', compact('purchaseEntry'));
    }

    public function update(UpdatePurchaseEntryRequest $request, $id)
    {
        $this->purchaseEntryRepository->update($id, $request->validated());
        return redirect()->route('purchase-entries.index')->with('success', 'Entrada de compra actualizada con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->purchaseEntryRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Entrada de compra eliminada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar la entrada de compra que se deseó eliminar.'], 404);
        }
    }
}
