<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispatchNoteRequest;
use App\Http\Requests\UpdateDispatchNoteRequest;
use App\Repositories\DispatchNoteRepository;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Illuminate\Http\Request;
use PDF;

class DispatchNoteController extends Controller
{
    protected $dispatchNoteRepository;

    public function __construct(DispatchNoteRepository $dispatchNoteRepository)
    {
        $this->dispatchNoteRepository = $dispatchNoteRepository;
    }

    /*
    * Muestra la vista de los remitos de la orden.
    */
    public function index($uuid)
    {
        $data = $this->dispatchNoteRepository->getOrderWithDispatchNotes($uuid);
        $order = $data['order'];
        $products = json_decode($order->products, true);
        $dispatchNotes = $data['dispatchNotes'];
        Log::info($dispatchNotes);
        return view('dispatch_notes.index', compact('order', 'products', 'dispatchNotes'));
    }

    /*
    * Obtiene todos los remitos.
    */
    public function getAll()
    {
        $dispatchNotes = $this->dispatchNoteRepository->getAll();
        return response()->json(['dispatchNotes' => $dispatchNotes]);
    }

    /*
    * Muestra el formulario para crear un remito.
    */
    public function create()
    {
        return view('dispatch_notes.create');
    }

    /*
    * Guarda un remito.
    */
    public function store(StoreDispatchNoteRequest $request)
    {
        $order = Order::findOrFail($request->order_id);
        $product = collect(json_decode($order->products, true))->firstWhere('id', $request->product_id);

        $totalDispatchedQuantity = $this->dispatchNoteRepository->getTotalDispatchedQuantity($order->id, $product['id']);

        if (($totalDispatchedQuantity + $request->quantity) > $product['quantity']) {
            return response()->json(['error' => 'No se puede guardar más metros que los pedidos en la orden.'], 400);
        }

        $dispatchNote = $this->dispatchNoteRepository->createDispatchNote(
            $request->validated(),
            $order,
            $product
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Nota de despacho creada con éxito.',
                'dispatchNote' => $dispatchNote,
            ]);
        }

        return redirect()->route('dispatch_notes.index')->with('success', 'Nota de despacho creada con éxito.');
    }

    /*
    * Muestra un remito.
    */
    public function show($id)
    {
        $dispatchNote = $this->dispatchNoteRepository->find($id);
        return view('dispatch_notes.show', compact('dispatchNote'));
    }

    /*
    * Muestra el formulario para editar un remito.
    */
    public function edit($id)
    {
        $dispatchNote = $this->dispatchNoteRepository->find($id);
        return view('dispatch_notes.edit', compact('dispatchNote'));
    }

    /*
    * Actualiza un remito.
    */
    public function update(UpdateDispatchNoteRequest $request, $id)
    {
        $updated = $this->dispatchNoteRepository->update($id, $request->validated());
        if ($updated) {
            return response()->json(['success' => 'Nota de despacho actualizada correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar actualizar la nota de despacho.'], 500);
        }
    }

    /*
    * Elimina un remito.
    */
    public function destroy($id)
    {
        $deleted = $this->dispatchNoteRepository->delete($id);
        if ($deleted) {
            return response()->json(['success' => 'Nota de despacho eliminada correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar la nota de despacho.'], 500);
        }
    }

    /*
    * Descarga varios remitos en formato PDF. Utilizado para descargar todos los remitos de una orden.
    */
    public function downloadMultiplePdf($uuid)
    {
        $data = $this->dispatchNoteRepository->getOrderWithDispatchNotes($uuid);
        $order = $data['order'];
        $products = json_decode($order->products, true);
        $dispatchNotes = $data['dispatchNotes'];

        $client = $order->client;

        $pdf = PDF::loadView('dispatch_notes.show-multiple', compact('order', 'products', 'dispatchNotes', 'client'));
        return $pdf->download('remitos-orden-' . $order->uuid . '.pdf');
    }

    /*
    * Descarga un solo remito en formato PDF.
    */
    public function downloadSinglePdf($id)
    {
        $dispatchNote = $this->dispatchNoteRepository->find($id);
        $order = $dispatchNote->order;
        $product = json_decode($order->products, true);

        $pdf = PDF::loadView('dispatch_notes.show-one', compact('dispatchNote', 'order', 'product'));
        return $pdf->download('remito-' . $dispatchNote->id . '.pdf');
    }
}
