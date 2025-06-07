<?php

namespace App\Http\Controllers;

use App\Repositories\LeadConversationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadConversationController extends Controller
{
    protected $leadConversationRepository;

    /**
     * Constructor que inyecta el repositorio de conversaciones
     */
    public function __construct(LeadConversationRepository $leadConversationRepository)
    {
        $this->leadConversationRepository = $leadConversationRepository;
    }

    /**
     * Obtiene todas las conversaciones asociadas a un lead
     */
    public function index($leadId)
    {
        $conversations = $this->leadConversationRepository->getAllByLead($leadId);
        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    /**
     * Almacena una nueva conversación para un lead
     */
    public function store(Request $request, $leadId)
    {
        try {
            $request->validate([
                'message' => 'required|string'
            ]);

            $conversation = $this->leadConversationRepository->create($leadId, $request->all());
            $conversation->load('creator');

            return response()->json([
                'success' => true,
                'message' => 'Conversación registrada con éxito.',
                'conversation' => $conversation
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear la conversación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la conversación.',
            ], 500);
        }
    }

    /**
     * Muestra una conversación específica
     */
    public function show($id)
    {
        try {
            $conversation = $this->leadConversationRepository->find($id);
            return response()->json([
                'success' => true,
                'conversation' => $conversation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Conversación no encontrada.'
            ], 404);
        }
    }

    /**
     * Actualiza una conversación existente
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'message' => 'required|string'
            ]);

            $conversation = $this->leadConversationRepository->update($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Conversación actualizada con éxito.',
                'conversation' => $conversation
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar la conversación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la conversación.',
            ], 500);
        }
    }

    /**
     * Elimina una conversación (soft delete)
     */
    public function destroy($leadId, $id)
    {
        try {
            $conversation = $this->leadConversationRepository->find($id);
            $conversation->update(['is_deleted' => 1]);
            
            return response()->json([
                'success' => true,
                'message' => 'Conversación eliminada con éxito.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar la conversación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la conversación.',
            ], 500);
        }
    }
}
