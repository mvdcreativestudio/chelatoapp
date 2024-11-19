<?php

namespace App\Models;

use App\Enums\Events\EventEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'event_type_id',
        'event_name',
    ];

    protected $casts = [
        'event_name' => EventEnum::class,
    ];


    public function getEventDescription(): string
    {
        return $this->event_name->getDescription();
    }

    /**
     * Obtiene el tipo de evento asociado a este evento.
     *
     * @return BelongsTo
     */
    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    /**
     * Obtiene las configuraciones de eventos de las tiendas que usan este evento.
     *
     * @return HasMany
     */
    public function eventStoreConfigurations(): HasMany
    {
        return $this->hasMany(EventStoreConfiguration::class);
    }
}
