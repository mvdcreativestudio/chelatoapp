<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyRateHistory extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
    */
    protected $table = 'currency_rate_histories';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
    */
    protected $fillable = ['currency_rate_id', 'date', 'buy', 'sell'];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
    */
    protected $casts = [
        'date' => 'date',
        'buy' => 'decimal:5',
        'sell' => 'decimal:5',
    ];

    /**
     * Obtiene la tasa de cambio asociada al historial de tasas de cambio.
     *
     * @return BelongsTo
    */
    public function currencyRate()
    {
        return $this->belongsTo(CurrencyRate::class);
    }
}
