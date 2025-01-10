<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Repositories\LeadRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UpdateLeadCompanyInformationRequest;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\LeadAssignment;

class LeadController extends Controller
{
    protected $leadRepository;

    /**
     * Constructor que inyecta el repositorio de leads
     */
    public function __construct(LeadRepository $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * Muestra la vista principal con todos los leads y usuarios
     */
    public function index()
    {
        $leads = $this->leadRepository->getAll();
        $users = User::all();
        return view('lead.index', compact('leads', 'users'));
    }

    /**
     * Retorna todos los leads en formato JSON
     */
    public function getAll()
    {
        $leads = $this->leadRepository->getAll();
        return response()->json(['leads' => $leads]);
    }

    /**
     * Muestra el formulario de creación de lead
     */
    public function create()
    {
        return view('lead.create');
    }

    /**
     * Almacena un nuevo lead en la base de datos
     */
    public function store(StoreLeadRequest $request)
    {
        $lead = $this->leadRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'leada creada con éxito.',
                'lead' => $lead
            ]);
        }

        return view('lead.index');
    }

    /**
     * Muestra la información de un lead específico
     */
    public function show($id)
    {
        $lead = $this->leadRepository->find($id);
        return response()->json([
            'success' => true,
            'lead' => $lead
        ]);
    }

    /**
     * Muestra el formulario de edición de un lead
     */
    public function edit($id)
    {
        $lead = $this->leadRepository->find($id);
        return view('lead.edit', compact('lead'));
    }

    /**
     * Actualiza la información de un lead existente
     */
    public function update(UpdateLeadRequest $request, $id)
    {
        $updated =   $this->leadRepository->update($id, $request->validated());
        if ($updated) {
            return response()->json(['success' => 'Fórmula eliminada correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar la fórmula.'], 500);
        }
    }

    /**
     * Elimina un lead de la base de datos
     */
    public function destroy($id)
    {
        $deleted = $this->leadRepository->delete($id);
        if ($deleted) {
            return response()->json(['success' => 'Lead eliminada correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar el lead.'], 500);
        }
    }

    /**
     * Actualiza la categoría y posición de un lead en el tablero Kanban
     */
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|integer',
            'position' => 'required|integer',
            'items_order' => 'required|array',
            'items_order.*.id' => 'required|integer',
            'items_order.*.position' => 'required|integer',
        ]);

        try {
            // Actualizar la posición de todos los elementos en la columna
            foreach ($request->items_order as $item) {
                $this->leadRepository->update($item['id'], [
                    'position' => $item['position']
                ]);
            }

            // Actualizar la categoría y posición del elemento arrastrado
            $updatedLead = $this->leadRepository->updateCategory($id, $request->category_id, $request->position);

            return response()->json([
                'success' => true,
                'message' => 'Categoría y posición actualizadas con éxito.',
                'lead' => $updatedLead,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar la categoría y posición del lead: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la categoría y posición del lead.',
            ], 500);
        }
    }

    /**
     * Actualiza la información de la compañía asociada al lead
     */
    public function updateCompanyInformation(UpdateLeadCompanyInformationRequest $request, $id)
    {
        try {
            $updated = $this->leadRepository->updateCompanyInformation($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Información de la compañía actualizada con éxito.',
                'data' => $updated
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar la información de la compañía: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la información de la compañía.',
            ], 500);
        }
    }

    /**
     * Convierte un lead en cliente
     */
    public function convertToClient($id)
    {
        try {
            $client = $this->leadRepository->convertToClient($id);
            $lead = $this->leadRepository->find($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Lead convertido a cliente exitosamente',
                'client' => $client,
                'lead' => $lead
            ]);
        } catch (\Exception $e) {
            Log::error('Error al convertir lead a cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al convertir el lead a cliente'
            ], 500);
        }
    }

    /**
     * Asigna un usuario a un lead
     */
    public function assignUser(Request $request, $leadId)
    {
        $userId = $request->input('user_id');
        try {
            $assignment = $this->leadRepository->assignUser($leadId, $userId);
            $lead = $this->leadRepository->find($leadId);

            return response()->json([
                'success' => true,
                'assignment' => $assignment,
                'lead' => $lead
            ]);
        } catch (\Exception $e) {
            Log::error('Error al asignar usuario: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al asignar usuario.'], 500);
        }
    }

    /**
     * Elimina la asignación de un usuario a un lead
     */
    public function removeAssignment($leadId, $userId)
    {
        try {
            $this->leadRepository->removeUserAssignment($leadId, $userId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error al remover asignación: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al remover asignación.'], 500);
        }
    }
}
