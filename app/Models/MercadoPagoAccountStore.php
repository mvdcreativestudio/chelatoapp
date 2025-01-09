<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MercadoPagoAccountStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'external_id',
        'street_number',
        'street_name',
        'city_name',
        'state_name',
        'latitude',
        'longitude',
        'reference',
        'store_id',
        'mercado_pago_account_id',
    ];

    /**
     * Relación con la cuenta de MercadoPago.
     */
    public function mercadopagoAccount()
    {
        return $this->belongsTo(MercadoPagoAccount::class, 'mercado_pago_account_id');
    }

    /**
     * Relación con las cajas registradoras.
     */

    public function mercadopagoAccountPOS()
    {
        return $this->hasMany(MercadoPagoAccountPOS::class);
    }

    /**
     * Método para obtener la dirección completa.
     *
     * @return string
     */
    public function getFullAddress(): string
    {
        return "{$this->street_number} {$this->street_name}, {$this->city_name}, {$this->state_name}";
    }
}
