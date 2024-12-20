<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventStoreConfiguration extends Model
{
    protected $fillable = [
        'store_id',
        'event_id',
        'enabled',
        'email_recipient',
    ];

    /**
     * Obtiene la tienda asociada a esta configuración de evento.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene el evento asociado a esta configuración de tienda.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
