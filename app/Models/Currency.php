<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'symbol',
        'name',
        'exchange_rate',
    ];

    /**
     * Obtiene los pagos de cuentas corrientes asociados a la moneda.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function currentAccountPayments()
    {
        return $this->hasMany(CurrentAccountPayment::class);
    }
    /**
     * Obtiene los asientos asociados a esta moneda.
     *
     * @return HasMany
     */
    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    /**
     * Obtiene las cuentas corrientes asociadas a esta moneda.
     *
     * @return HasMany
     */

    public function currentAccounts()
    {
        return $this->hasMany(CurrentAccount::class);
    }
}
