<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MercadoPagoAccountPOS extends Model
{
    use HasFactory;

    protected $table = 'mercado_pago_account_pos';

    protected $fillable = [
        'id_pos',
        'name',
        'fixed_amount',
        'category',
        'qr_image',
        'template_document',
        'template_image',
        'qr_code',
        'store_id',
        'external_id',
        'external_store_id',
        'cash_register_id',
        'mercado_pago_account_store_id',
    ];

    /**
     * Relación con la sucursal de MercadoPago.
     */
    public function mercadoPagoAccountStore()
    {
        return $this->belongsTo(MercadoPagoAccountStore::class);
    }

    /**
     * Relación con la caja registradora.
     */
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }
}
