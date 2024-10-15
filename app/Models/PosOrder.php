<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'hour',
        'cash_register_log_id',
        'cash_sales',
        'pos_sales',
        'discount',
        'client_id',
        'products',
        'subtotal',
        'total',
        'notes'
    ];
    
    /**
     * Obtiene la caja registradora asociada al log.
     *
     * @return BelongsTo
     */
    public function cashRegisterLog(): BelongsTo
    {
        return $this->belongsTo(CashRegisterLog::class);
    }

    /**
     * Obtiene la caja registradora asociada al log.
     *
     * @return BelongsTo
     */
    public function clientId(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
