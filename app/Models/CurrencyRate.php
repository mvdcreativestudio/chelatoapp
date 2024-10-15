<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurrencyRate extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
    */
    protected $table = 'currency_rates';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
    */
    protected $fillable = [
        'name',
    ];

    /**
     * Obtiene el historial de tasas de cambio asociado a la tasa de cambio.
     *
     * @return HasMany
    */
    public function histories(): HasMany
    {
        return $this->hasMany(CurrencyRateHistory::class);
    }
}
