<?php

namespace App\Models;

use App\Enums\Events\EventTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    protected $fillable = [
        'event_type_name',
    ];

    protected $casts = [
        'event_type_name' => EventTypeEnum::class,
    ];

    public function getTypeDescription(): string
    {
        return $this->event_type_name->getDescription();
    }

    /**
     * Obtiene los eventos asociados a este tipo de evento.
     *
     * @return HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
