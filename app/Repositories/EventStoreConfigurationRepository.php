<?php

namespace App\Repositories;

use App\Models\Event;
use App\Models\EventStoreConfiguration;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventStoreConfigurationRepository
{
    /**
     * Obtiene todas las configuraciones de eventos para una tienda específica.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getConfigurationsByStore(int $storeId): array
    {
        $store = Store::findOrFail($storeId);

        // Obtener todos los eventos con sus tipos de eventos
        $events = Event::with('eventType')->get();

        // Obtener las configuraciones de eventos activas de la tienda
        $activeConfigurations = EventStoreConfiguration::where('store_id', $storeId)->get()->keyBy('event_id');

        // Preparar la lista de eventos con su estado de activación
        $eventConfigurations = $events->map(function ($event) use ($activeConfigurations) {
            $isActive = $activeConfigurations->has($event->id) && $activeConfigurations[$event->id]->enabled;

            return [
                'event' => $event,
                'is_active' => $isActive,
                'email_recipient' => $isActive ? $activeConfigurations[$event->id]->email_recipient : null,
            ];
        });

        // Calcular totales para las tarjetas
        $totalEvents = $events->count();
        $activeEvents = $eventConfigurations->where('is_active', true)->count();
        $inactiveEvents = $totalEvents - $activeEvents;

        return compact('store', 'eventConfigurations', 'totalEvents', 'activeEvents', 'inactiveEvents');
    }

    /**
     * Activa o desactiva la configuración de un evento para una tienda específica.
     *
     * @param int $storeId
     * @param int $eventId
     * @param bool $isActive
     * @return EventStoreConfiguration
     */
    public function toggleEvent(int $storeId, int $eventId, bool $isActive): EventStoreConfiguration
    {
        // dd($storeId, $eventId, $isActive);
        try {
            $configuration = EventStoreConfiguration::updateOrCreate(
                ['store_id' => $storeId, 'event_id' => $eventId],
                ['enabled' => $isActive]
            );

            return $configuration;
        } catch (\Exception $e) {
            throw new \Exception('Error al activar o desactivar la configuración de evento: ' . $e->getMessage());
        }
    }

    /**
     * Crea o actualiza la configuración de un evento para una tienda.
     *
     * @param int $storeId
     * @param array $data
     * @return EventStoreConfiguration
     */
    public function createOrUpdateConfiguration(int $storeId, array $data): EventStoreConfiguration
    {
        DB::beginTransaction();
        try {
            $configuration = EventStoreConfiguration::updateOrCreate(
                ['store_id' => $storeId, 'event_id' => $data['event_id']],
                [
                    'enabled' => $data['enabled'],
                    'email_recipient' => $data['email_recipient'],
                    'custom_threshold' => $data['custom_threshold'] ?? null,
                ]
            );

            DB::commit();
            return $configuration;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al crear o actualizar la configuración de evento: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una configuración de evento específica.
     *
     * @param int $id
     * @return bool
     */
    public function deleteConfiguration(int $id): bool
    {
        try {
            $configuration = EventStoreConfiguration::findOrFail($id);
            $configuration->delete();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar la configuración de evento: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene todas las configuraciones de eventos activas para una tienda específica.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getActiveConfigurationsByStore(int $storeId): Collection
    {
        return EventStoreConfiguration::with('event')
            ->where('store_id', $storeId)
            ->where('enabled', true)
            ->get();
    }


    /**
     * Verifica si un evento está habilitado para una tienda específica.
     *
     * @param int $storeId
     * @param Event $event
     * @return bool
     */

    public function isEventEnabledForStore(int $storeId, Event $event): bool
    {
        return EventStoreConfiguration::where('store_id', $storeId)
            ->where('event_id', $event->id)
            ->where('enabled', true)
            ->exists();
    }
}
