<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount_paid',
        'payment_date',
        'expense_id',
        'payment_method_id',
    ];

    /**
     * Obtiene el gasto asociado al pago parcial.
     *
     * @return BelongsTo
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Obtiene el método de pago asociado al pago parcial.
     *
     * @return BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
