<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CFE extends Model
{
    use HasFactory;

    protected $table = 'cfes';

    protected $fillable = [
      'order_id', 'store_id', 'type', 'serie', 'nro',
      'caeNumber', 'caeRange', 'caeExpirationDate', 'total', 'currency', 'status',
      'emitionDate', 'sentXmlHash', 'securityCode', 'qrUrl', 'cfeId', 'reason', 'balance', 'main_cfe_id', 'received', 'is_receipt', 'issuer_name'
    ];

    protected $casts = [
      'caeRange' => 'array',
      'caeExpirationDate' => 'date',
      'emitionDate' => 'datetime',
    ];

    /**
     * Obtiene la orden asociada al recibo.
     *
     * @return BelongsTo
    */
    public function order(): BelongsTo
    {
      return $this->belongsTo(Order::class);
    }

    /**
     * Obtiene la tienda asociada al recibo.
     *
     * @return BelongsTo
    */
    public function store(): BelongsTo
    {
      return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene CFE principal
     *
     * @return BelongsTo
    */
    public function mainCfe(): BelongsTo
    {
      return $this->belongsTo(CFE::class, 'main_cfe_id');
    }

    /**
     * Obtiene los CFEs asociados al CFE principal
     *
     * @return HasMany
    */
    public function relatedCfes(): HasMany
    {
      return $this->hasMany(CFE::class, 'main_cfe_id');
    }
}
