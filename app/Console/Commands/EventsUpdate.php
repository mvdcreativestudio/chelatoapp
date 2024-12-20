<?php

namespace App\Console\Commands;

use App\Enums\Events\EventEnum;
use App\Enums\Events\EventTypeEnum;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Console\Command;

class EventsUpdate extends Command
{
    protected $signature = 'events:update';
    protected $description = 'Actualiza los tipos de eventos y eventos en la base de datos';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Crear o actualizar los tipos de eventos
        foreach (EventTypeEnum::cases() as $eventType) {
            $eventTypeModel = EventType::firstOrCreate(
                ['event_type_name' => $eventType->value],
                ['created_at' => now(), 'updated_at' => now()]
            );

            if ($eventTypeModel->wasRecentlyCreated) {
                $this->info("Tipo de evento '{$eventType->value}' creado.");
            } else {
                $this->info("Tipo de evento '{$eventType->value}' ya existente, actualizado.");
            }

            // Insertar o actualizar los eventos asociados a cada tipo de evento
            if (isset(EventEnum::getAssociatedTypeEvents()[$eventType->value])) {
                $eventNames = EventEnum::getAssociatedTypeEvents()[$eventType->value];
                foreach ($eventNames as $eventName) {
                    $eventModel = Event::firstOrCreate(
                        [
                            'event_type_id' => $eventTypeModel->id,
                            'event_name' => $eventName,
                        ],
                        ['created_at' => now(), 'updated_at' => now()]
                    );

                    if ($eventModel->wasRecentlyCreated) {
                        $this->info("Evento '{$eventName}' creado para tipo '{$eventType->value}'.");
                    } else {
                        $this->info("Evento '{$eventName}' ya existente para tipo '{$eventType->value}', actualizado.");
                    }
                }
            }
        }

        $this->info('Actualización de eventos y tipos de eventos completada.');
    }
}
