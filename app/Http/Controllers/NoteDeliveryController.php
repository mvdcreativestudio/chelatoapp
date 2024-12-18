<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteDeliveryRequest;
use App\Http\Requests\UpdateNoteDeliveryRequest;
use App\Repositories\NoteDeliveryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NoteDeliveriesExport;

class NoteDeliveryController extends Controller
{
    protected $noteDeliveryRepository;

    public function __construct(NoteDeliveryRepository $noteDeliveryRepository)
    {
        $this->noteDeliveryRepository = $noteDeliveryRepository;
    }

    /*
    * Muestra una lista de notas de entrega.
    */
    public function index()
    {
        $noteDeliveries = $this->noteDeliveryRepository->getAll();
        return view('note_deliveries.index', compact('noteDeliveries'));
    }

    /*
    * Muestra el formulario para crear una nueva nota de entrega.
    */
    public function create()
    {
        return view('note_deliveries.create');
    }

    /*
    * Guarda una nueva nota de entrega.
    */
    public function store(StoreNoteDeliveryRequest $request)
    {
        $noteDelivery = $this->noteDeliveryRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Nota de entrega creada con éxito.',
                'noteDelivery' => $noteDelivery,
            ]);
        }

        return redirect()->route('note_deliveries.index')
            ->with('success', 'Nota de entrega creada con éxito.');
    }

    /*
    * Muestra una nota de entrega.
    */
    public function show($id)
    {
        $noteDelivery = $this->noteDeliveryRepository->find($id);
        return view('note_deliveries.show', compact('noteDelivery'));
    }

    /*
    * Muestra el formulario para editar una nota de entrega.
    */
    public function edit($id)
    {
        $noteDelivery = $this->noteDeliveryRepository->find($id);
        return view('note_deliveries.edit', compact('noteDelivery'));
    }

    /*
    * Actualiza una nota de entrega.
    */
    public function update(UpdateNoteDeliveryRequest $request, $id)
    {
        $updated = $this->noteDeliveryRepository->update($id, $request->validated());

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Nota de entrega actualizada con éxito.',
                'noteDelivery' => $updated
            ]);
        }

        return response()->json([
            'error' => 'Error al actualizar la nota de entrega.'
        ], 500);
    }

    /*
    * Elimina una nota de entrega.
    */
    public function destroy($id)
    {
        $deleted = $this->noteDeliveryRepository->delete($id);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Nota de entrega eliminada con éxito.'
            ]);
        }

        return response()->json([
            'error' => 'Error al eliminar la nota de entrega.'
        ], 500);
    }

    /*
    * Obtiene la información necesaria para el formulario de creación de notas de entrega.
    */
    public function getInfoForForm()
    {
        $data = $this->noteDeliveryRepository->getInfoForForm();
        return response()->json($data);
    }

    /*
    * Obtiene los detalles de una nota de entrega.
    */
    public function getDeliveryDetails($id)
    {
        $noteDelivery = $this->noteDeliveryRepository->find($id);
        return response()->json($noteDelivery);
    }

    /*
    * Exporta las notas de entrega a un archivo Excel.
    */
    public function export()
    {
        return Excel::download(new NoteDeliveriesExport, 'envios.xlsx');
    }
}
