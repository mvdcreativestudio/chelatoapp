<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegisterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'open_time',
        'close_time',
        'cash_sales',
        'pos_sales',
        'cash_float',
    ];

    /**
     * Obtiene la caja registradora asociada al log.
     *
     * @return BelongsTo
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Obtiene las ordenes asociadas al log de la caja registradora.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(PosOrder::class);
    }
}
