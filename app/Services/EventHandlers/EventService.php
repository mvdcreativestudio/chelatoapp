<?php

namespace App\Services\EventHandlers;

use App\Enums\Events\EventEnum;
use App\Models\Event;
use App\Repositories\EventStoreConfigurationRepository;
use App\Repositories\StoreRepository;
use App\Services\EventHandlers\Handlers\LowStockHandler;
use App\Services\EventHandlers\Interface\EventHandlerInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class EventService
{
    protected $eventConfigRepo;
    protected $handlers;
    protected $storeRepository;

    public function __construct(EventStoreConfigurationRepository $eventConfigRepo, StoreRepository $storeRepository)
    {
        $this->eventConfigRepo = $eventConfigRepo;
        $this->storeRepository = $storeRepository;

        $this->handlers = [
            EventEnum::LOW_STOCK->value => new LowStockHandler($this->storeRepository),
            // Otros handlers...
        ];
    }

    public function handleEvents(int $storeId, array $events, array $data = [])
    {
        foreach ($events as $eventEnum) {
            try {
                $event = Event::where('event_name', $eventEnum->value)->first();
                $data['event_id'] = $event->id;

                if (!$event) {
                    Log::error("Evento no encontrado en la base de datos: {$eventEnum->getDescription()}");
                    throw new Exception("Evento no encontrado en la base de datos: {$eventEnum->getDescription()}");
                }
                if ($this->eventConfigRepo->isEventEnabledForStore($storeId, $event)) {
                    $this->executeEvent($storeId, $eventEnum, $data);
                }
            } catch (Exception $e) {
                dd($e);
                Log::error("Error al manejar el evento {$eventEnum->getDescription()}: " . $e->getMessage());
                throw new Exception("Error al manejar el evento {$eventEnum->getDescription()}: " . $e->getMessage(), 0, $e);
            }
        }
    }

    protected function executeEvent(int $storeId, EventEnum $event, array $data = [])
    {
        $handler = $this->handlers[$event->value] ?? null;

        if ($handler && $handler instanceof EventHandlerInterface) {
            try {
                return $handler->handle($storeId, $data);
            } catch (Exception $e) {
                Log::error("Error al ejecutar el evento {$event->getDescription()}: " . $e->getMessage());
                throw new Exception("Error al ejecutar el evento {$event->getDescription()}: " . $e->getMessage(), 0, $e);
            }

            Log::info("Evento {$event->getDescription()} manejado correctamente.");
        } else {
            Log::error("No se encontró un manejador para el evento: {$event->getDescription()}");
            throw new Exception("No se encontró un manejador para el evento: {$event->getDescription()}");
        }
    }
}
