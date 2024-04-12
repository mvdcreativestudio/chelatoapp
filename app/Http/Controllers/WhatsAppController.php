<?php

namespace App\Http\Controllers;

use App\Repositories\WhatsAppRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * El repositorio para las operaciones de WhatsApp.
    */
    protected WhatsAppRepository $whatsAppRepo;

    /**
     * Inicializa el controlador
     *
     * @param WhatsAppRepository $whatsAppRepo
    */
    public function __construct(WhatsAppRepository $whatsAppRepo)
    {
        $this->whatsAppRepo = $whatsAppRepo;
    }

    /**
     * Webhook encargado de recibir los mensajes enviados y recibidos por WhatsApp.
     *
     * @return void
    */
    public function webhook()
    {
        $this->whatsAppRepo->webhook();
    }

    /**
     * Procesa los mensajes recibidos de WhatsApp.
     *
     * @return void
    */
    public function recibe()
    {
        $this->whatsAppRepo->recibe();
    }

    /**
     * Maneja la solicitud para buscar mensajes entre el número de teléfono de la tienda del usuario y otro número.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function fetchMessages(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $contactPhoneNumber = $request->input('phone_number');

        $result = $this->whatsAppRepo->fetchMessagesForUserStore($userId, $contactPhoneNumber);

        if (array_key_exists('error', $result)) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json(['messages' => $result['messages']]);
    }
}
