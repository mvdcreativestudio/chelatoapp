<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosDevice extends Model
{
    protected $fillable = ['name', 'pos_provider_id', 'identifier', 'user', 'cash_register'];

    /**
     * Obtiene el proveedor asociado a este dispositivo POS.
     *
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(PosProvider::class);
    }

    /**
     * Obtiene la tienda asociada a este dispositivo POS.
     *
     * @return BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Relación con cajas registradoras.
     *
     * @return BelongsToMany
     */
    public function cashRegisters(): BelongsToMany
    {
        return $this->belongsToMany(CashRegister::class, 'cash_register_pos_device', 'pos_device_id', 'cash_register_id');
    }

}
