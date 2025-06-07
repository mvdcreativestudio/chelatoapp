<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Modelo Entry: representa un asiento contable.
 */
class Entry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entry_date',
        'entry_type_id',
        'concept',
        'currency_id',
        'is_balanced',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_balanced' => 'boolean',
    ];

    /**
     * Obtiene el total del Haber del asiento.
     *
     * @return float
     */
    public function getTotalDebitAttribute(): float
    {
        return $this->details->sum('amount_debit');
    }

    /**
     * Obtiene el total del Debe del asiento.
     *
     * @return float
     */
    public function getTotalCreditAttribute(): float
    {
        return $this->details->sum('amount_credit');
    }

    /**
     * Obtiene el tipo de asiento asociado.
     *
     * @return BelongsTo
     */
    public function entryType()
    {
        return $this->belongsTo(EntryType::class);
    }

    /**
     * Obtiene la moneda asociada al asiento.
     *
     * @return BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Obtiene los detalles asociados al asiento.
     *
     * @return HasMany
     */
    public function details()
    {
        return $this->hasMany(EntryDetail::class);
    }

    /**
     * Calcula si el asiento está balanceado.
     *
     * @return bool
     */

    public function calculateBalance(): bool
    {
        return $this->getTotalDebitAttribute() === $this->getTotalCreditAttribute();
    }
}
