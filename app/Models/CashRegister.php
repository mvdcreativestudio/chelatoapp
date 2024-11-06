<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
    ];

    /**
     * Obtiene la tienda asociada a la caja registradora.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene el usuario asociado a la caja registradora.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene los logs de la caja registradora.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(CashRegisterLog::class);
    }

    /**
     * Devuelve el estado de la caja y la clase CSS asociada.
     *
     * @return array
     */
    public function getEstado()
    {
        if (is_null($this->open_time) && is_null($this->close_time)) {
            return [
                'estado' => 'No Iniciada',
                'clase' => 'bg-warning'
            ];
        } elseif (is_null($this->close_time)) {
            return [
                'estado' => 'Abierta',
                'clase' => 'bg-success'
            ];
        } else {
            return [
                'estado' => 'Cerrada',
                'clase' => 'bg-danger'
            ];
        }
    }

    /**
     * Obtiene los dispositivos POS asociados a la caja registradora.
     *
     * @return BelongsToMany
     */
    public function posDevices(): BelongsToMany
    {
        return $this->belongsToMany(PosDevice::class, 'cash_register_pos_device');
    }
}
