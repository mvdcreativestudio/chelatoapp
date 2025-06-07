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
use InvalidArgumentException;

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
        try {
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
                
        } catch (InvalidArgumentException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Error al crear la nota de entrega.'
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear la nota de entrega.']);
        }
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
        try {
            $noteDelivery = $this->noteDeliveryRepository->find($id);

            // Si el usuario no tiene permiso, solo permitimos la edición de campos sin valor
            if (!auth()->user()->can('access_edit_delivery_data')) {
                $editableFields = [
                    'departuring',
                    'arriving',
                    'unload_starting',
                    'unload_finishing',
                    'departure_from_site',
                    'return_to_plant'
                ];

                foreach ($editableFields as $field) {
                    // Si el campo ya tiene un valor y en la request se intenta modificar
                    if (isset($request[$field]) && $noteDelivery->$field !== null) {
                        // Formateamos ambos valores a "Y-m-d\TH:i" para omitir segundos
                        $dbValue = \Carbon\Carbon::parse($noteDelivery->$field)
                                    ->format('Y-m-d\TH:i');
                        $reqValue = \Carbon\Carbon::parse($request[$field])
                                    ->format('Y-m-d\TH:i');
                                    
                        if ($reqValue != $dbValue) {
                            return response()->json([
                                'error' => 'No tienes permiso para re-editar este dato.'
                            ], 403);
                        }
                    }
                }
            }

            $updated = $this->noteDeliveryRepository->update($id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Nota de entrega actualizada con éxito.',
                'noteDelivery' => $updated
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la nota de entrega.'
            ], 500);
        }
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
