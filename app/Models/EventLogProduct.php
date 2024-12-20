<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLogProduct extends Model
{
    protected $fillable = [
        'store_id',
        'event_id',
        'product_id',
        'alert_sent_at',
    ];

    /**
     * Obtiene la tienda asociada al log de evento.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene el evento asociado al log.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Obtiene el producto asociado al log de evento (opcional).
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
