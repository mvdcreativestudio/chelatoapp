<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrentAccountPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_amount',
        'payment_date',
        'payment_method_id',
        'current_account_id',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    /**
     * Obtiene la cuenta corriente asociada a este pago parcial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    // public function initialCredit()
    // {
    //     return $this->belongsTo(CurrentAccountInitialCredit::class);
    // }


    /**
     * Obtiene la cuenta corriente asociada a este pago parcial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentAccount()
    {
        return $this->belongsTo(CurrentAccount::class);
    }

    /**
     * Obtiene el método de pago asociado a este pago parcial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
