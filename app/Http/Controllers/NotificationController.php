<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\NotificationRepository;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function index(): JsonResponse
    {
        try {
            $unreadNotifications = $this->notificationRepository->getUnreadNotifications();
            return response()->json($unreadNotifications);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $notificationIds = $request->input('notification_ids', []);

        try {
            $affectedRows = $this->notificationRepository->markNotificationsAsRead($notificationIds);
            return response()->json(['message' => 'Notificaciones marcadas como leídas', 'affected' => $affectedRows], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode();
            if ($statusCode < 100 || $statusCode >= 600) {
                $statusCode = 500; // Default to internal server error if the status code is invalid
            }
            return response()->json([
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }
}
