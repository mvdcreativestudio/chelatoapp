<?php

namespace App\Models;

use App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum;
use Illuminate\Database\Eloquent\Model;

class MercadoPagoAccount extends Model
{
    protected $table = 'mercadopago_accounts'; // Especifica el nombre de la tabla si no sigue la convención de nombres de Laravel

    protected $fillable = [
        'store_id',
        'public_key',
        'access_token',
        'secret_key',
        'type',
        'user_id_mp'
    ];

    protected $casts = [
        'type' => MercadoPagoApplicationTypeEnum::class,
    ];

    // get type
    public function getTypeDescription(): string
    {
        return $this->type->getDescription();
    }

    public function mercadopagoAccountStore()
    {
        return $this->hasMany(MercadoPagoAccountStore::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
