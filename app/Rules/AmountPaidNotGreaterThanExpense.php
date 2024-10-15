<?php

namespace App\Rules;

use App\Models\Expense;
use Illuminate\Contracts\Validation\Rule;

class AmountPaidNotGreaterThanExpense implements Rule
{
    protected $expenseId;
    protected $expensePaymentMethod;

    public function __construct($expenseId, $expensePaymentMethod)
    {
        $this->expenseId = $expenseId;
        $this->expensePaymentMethod = $expensePaymentMethod;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $expense = Expense::with(['payments' => function ($query) {
            if ($this->expensePaymentMethod) {
                $query->where('id', '!=', $this->expensePaymentMethod->id);
            }
        }])->find($this->expenseId);

        if (!$expense) {
            return false;
        }

        $totalPayments = $expense->amount - $expense->payments->sum('amount_paid');

        return $value <= $totalPayments;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El monto a abonar no puede ser mayor al monto del gasto.';
    }
}