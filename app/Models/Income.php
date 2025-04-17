<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'income_name',
        'income_description',
        'income_date',
        'income_amount',
        'payment_method_id',
        'income_category_id',
        'currency_id',
        'client_id',
        'supplier_id',
        'items',
        'cash_register_log_id',
        'tax_rate_id',
        'currency_rate',
        'currency',
        'is_billed',
    ];

    protected $casts = [
        'income_date' => 'datetime',
        'items' => 'array',
    ];

    /**
     * Obtiene los detalles de los CFE y aplica la lógica personalizada.
     *
     * @return objet|null
     */
    public function getReturnNoteCreditAttribute()
    {
        // Obtener todos los CFE asociados a esta orden y filtrar por tipo 101 o 111
        $cfe = $this->cfes
                    ->filter(function ($cfe) {
                        return in_array($cfe->type, [101, 111]);
                    })
                    ->sortByDesc('created_at') // Ordenar por la fecha de creación descendente
                    ->first(); // Obtener el más reciente

        // Retornar null si no se encuentra ninguna nota de crédito
        if (!$cfe) return null;

        // Verificar si el balance es 0
        return $cfe->balance;
    }

    /**
     * Determina si se puede emitir una factura.
     *
     * @return bool
     */
    public function canEmitInvoice()
    {
        return !$this->is_billed || ($this->getReturnNoteCreditAttribute() !== null && $this->getReturnNoteCreditAttribute() == 0);
    }

    /**
     * Determina si se puede emitir una nota de crédito.
     * @return bool
     */
    public function canEmitCreditNote()
    {
        // Obtener todos los CFE asociados a esta orden y filtrar por tipo 102 correspondiente a la nota de crédito
        $cfe = $this->cfes
                    ->filter(function ($cfe) {
                        return in_array($cfe->type, [102]);
                    })
                    ->sortByDesc('created_at')
                    ->first();
        return $this->is_billed && $this->getReturnNoteCreditAttribute() > 0;
    }

    /**
     * Determina si se puede emitir un recibo.
     * @return bool
     */

    public function canEmitReceipt()
    {
        $cfe = $this->cfes
                    ->filter(function ($cfe) {
                        return in_array($cfe->type, [101]);
                    })
                    ->sortByDesc('created_at')
                    ->first();

        return $this->is_billed && $cfe->balance > 0;
    }

    public function getLastInvoiceAttribute()
    {
        return $this->cfes->sortByDesc('created_at')->first();
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // Relación con IncomeCategory
    public function incomeCategory()
    {
        return $this->belongsTo(IncomeCategory::class);
    }

    // Relación con Currency
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    // Relación con Client (opcional)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación con Supplier (opcional)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Agregar relación con CashRegisterLog
    public function cashRegisterLog()
    {
        return $this->belongsTo(CashRegisterLog::class);
    }

    // Modificar la relación con Store para usar el cashRegisterLog
    public function store()
    {
        return $this->belongsTo(Store::class)
            ->withDefault(function ($store, $income) {
                return $income->cashRegisterLog->cashRegister->store;
            });
    }

    // Relación con TaxRate
    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }

    /**
     * Relación polimórfica: obtiene los CFEs asociados a este ingreso.
     *
     * @return MorphMany
     */
    public function cfes(): MorphMany
    {
        return $this->morphMany(CFE::class, 'cfeable');
    }
}