<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo EntryDetail: representa un detalle de un asiento contable.
 */
class EntryDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entry_id',
        'entry_account_id',
        'amount_debit',
        'amount_credit',
    ];

    /**
     * Obtiene el asiento al que pertenece el detalle.
     *
     * @return BelongsTo
     */
    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Obtiene la cuenta contable asociada al detalle.
     *
     * @return BelongsTo
     */
    public function entryAccount()
    {
        return $this->belongsTo(EntryAccount::class);
    }
}
