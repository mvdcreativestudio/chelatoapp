<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CashRegisterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'name',
        'open_time',
        'close_time',
        'cash_sales',
        'pos_sales',
        'mercadopago_sales',
        'bank_transfer_sales',
        'internal_credit_sales',
        'cash_float',
        'cash_expenses',
        'actual_cash',
        'cash_difference',
    ];

    protected $casts = [
        'open_time' => 'datetime',
        'close_time' => 'datetime',
    ];

    /**
     * Obtiene la caja registradora asociada al log.
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Obtiene las ordenes asociadas al log de la caja registradora.
     */
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'cash_register_log_id');
    }

    /**
     * Obtiene los gastos asociados al log de la caja registradora.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Calcula el total de egresos de esta caja convertidos a pesos.
     */
    public function getTotalExpenses()
    {
        $totalExpenses = 0;

        foreach ($this->expenses as $expense) {
            if ($expense->currency === 'Dólar') {
                $rate = $expense->currency_rate ?? $this->getDollarExchangeRate();
                $totalExpenses += $expense->amount * $rate;
            } else {
                $totalExpenses += $expense->amount;
            }
        }

        return $totalExpenses;
    }

    /**
     * Obtiene las ventas en efectivo calculadas dinámicamente si la caja está abierta.
     */
    public function getCurrentCashSales()
    {
        if ($this->close_time) {
            return $this->cash_sales ?? 0;
        }

        return $this->getEffectiveSalesByPaymentMethod(['cash']);
    }

    /**
     * Obtiene las ventas POS calculadas dinámicamente si la caja está abierta.
     */
    public function getCurrentPosSales()
    {
        if ($this->close_time) {
            return $this->pos_sales ?? 0;
        }

        return $this->getEffectiveSalesByPaymentMethod(['credit', 'debit', 'card']);
    }

    /**
     * Obtiene las ventas por Mercadopago calculadas dinámicamente si la caja está abierta.
     */
    public function getCurrentMercadopagoSales()
    {
        if ($this->close_time) {
            return $this->mercadopago_sales ?? 0;
        }

        return $this->getEffectiveSalesByPaymentMethod(['mercadopago']);
    }

    /**
     * Obtiene las ventas por transferencia bancaria calculadas dinámicamente si la caja está abierta.
     */
    public function getCurrentBankTransferSales()
    {
        if ($this->close_time) {
            return $this->bank_transfer_sales ?? 0;
        }

        return $this->getEffectiveSalesByPaymentMethod(['bankTransfer']);
    }

    /**
     * Obtiene las ventas por crédito interno calculadas dinámicamente si la caja está abierta.
     */
    public function getCurrentInternalCreditSales()
    {
        if ($this->close_time) {
            return $this->internal_credit_sales ?? 0;
        }

        return $this->getEffectiveSalesByPaymentMethod(['internalCredit']);
    }

    /**
     * Calcula las ventas efectivas para los métodos de pago indicados,
     * excluyendo órdenes reembolsadas y descontando notas de crédito en reembolsos parciales.
     */
    private function getEffectiveSalesByPaymentMethod(array $paymentMethods): float
    {
        $total = $this->orders()
            ->whereIn('payment_method', $paymentMethods)
            ->where('payment_status', '!=', 'refunded')
            ->sum('total');

        $partialRefundedOrderIds = $this->orders()
            ->whereIn('payment_method', $paymentMethods)
            ->where('payment_status', 'partial_refunded')
            ->pluck('id');

        if ($partialRefundedOrderIds->isNotEmpty()) {
            $creditNotesTotal = \App\Models\CFE::whereIn('order_id', $partialRefundedOrderIds)
                ->whereIn('type', [102, 112])
                ->sum('total');
            $total -= $creditNotesTotal;
        }

        return (float) $total;
    }

    /**
     * Obtiene el total de ventas (todos los métodos de pago).
     */
    public function getTotalSales()
    {
        return $this->getCurrentCashSales()
             + $this->getCurrentPosSales()
             + $this->getCurrentMercadopagoSales()
             + $this->getCurrentBankTransferSales()
             + $this->getCurrentInternalCreditSales();
    }

    /**
     * Calcula el saldo final en efectivo considerando egresos convertidos a pesos.
     */
    public function getFinalCashBalance()
    {
        return ($this->cash_float ?? 0) + $this->getCurrentCashSales() - $this->getTotalExpenses();
    }

    /**
     * Obtiene el efectivo final formateado para mostrar en vistas.
     */
    public function getFormattedFinalCashBalance()
    {
        return number_format($this->getFinalCashBalance(), 0, ',', '.');
    }

    /**
     * Obtiene la cotización actual del dólar.
     */
    private function getDollarExchangeRate(): float
    {
        try {
            $dollarRate = \App\Models\CurrencyRate::where('name', 'Dólar')->first();

            if (!$dollarRate) {
                return 1;
            }

            $latestRate = \App\Models\CurrencyRateHistory::where('currency_rate_id', $dollarRate->id)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            return $latestRate ? $latestRate->sell : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }
}
