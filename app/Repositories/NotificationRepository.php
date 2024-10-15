<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotificationCollection;

class NotificationRepository
{
    /**
     * Obtiene las notificaciones no leídas del usuario autenticado.
     *
     * @return DatabaseNotificationCollection
     */
    public function getUnreadNotifications(): DatabaseNotificationCollection
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado', 401);
        }

        return $user->unreadNotifications;
    }

    /**
     * Marca las notificaciones como leídas basadas en sus IDs.
     *
     * @param array $notificationIds
     * @return int
     */
    public function markNotificationsAsRead(array $notificationIds): int
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado', 401);
        }

        $affectedRows = 0;

        if ($notificationIds) {
            // Obtener las notificaciones para obtener los datos del order_id
            $notifications = $user->notifications()->whereIn('id', $notificationIds)->get();

            foreach ($notifications as $notification) {
                $orderId = $notification->data['order_id'];

                if ($orderId) {
                    // Marcar como leídas todas las notificaciones con el mismo order_id
                    $affected = $user->notifications()
                        ->where('data->order_id', $orderId)
                        ->update(['read_at' => now()]);

                    $affectedRows += $affected;
                }
            }
        }

        return $affectedRows;
    }
}
