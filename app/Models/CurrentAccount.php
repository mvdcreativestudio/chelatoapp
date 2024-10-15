<?php

namespace App\Models;

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use App\Enums\CurrentAccounts\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrentAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'transaction_type',
        'client_id',
        'supplier_id',
        'currency_id',
    ];

    protected $casts = [
        'status' => StatusPaymentEnum::class,
        'transaction_type' => TransactionTypeEnum::class,
    ];

    protected $appends = ['payment_total_debit', 'payment_amount'];

    protected $hidden = ['initialCredits', 'client', 'supplier', 'currency'];

    public function getPaymentAmountAttribute()
    {
        return $this->payments->sum('payment_amount');
    }

    public function getPaymentTotalDebitAttribute()
    {
        return $this->initialCredits->sum('total_debit');
    }
    /**
     * Obtiene los pagos parciales asociados a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function initialCredits()
    {
        return $this->hasMany(CurrentAccountInitialCredit::class);
    }

    /**
     * Obtiene los pagos parciales asociados a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(CurrentAccountPayment::class);
    }


    /**
     * Obtiene el cliente asociado a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtiene el proveedor asociado a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene la moneda asociada a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
