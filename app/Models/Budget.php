<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model {
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lead_id',
        'order_id',
        'price_list_id',
        'store_id',
        'due_date',
        'notes',
        'total',
        'discount_type',
        'discount',
        'is_blocked'
    ];

    /**
     * Obtiene los items asociados al presupuesto.
     *
     * @return HasMany
     */
    public function items() {
        return $this->hasMany(BudgetItem::class);
    }

    /**
     * Obtiene los pagos asociados al presupuesto.
     *
     * @return HasMany
     */
    public function status() {
        return $this->hasMany(BudgetStatus::class);
    }

    /**
     * Obtiene el cliente asociado al presupuesto.
     *
     * @return BelongsTo
     */
    public function client() {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtiene la orden asociada al presupuesto.
     *
     * @return BelongsTo
     */
    public function lead() {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Obtiene la orden asociada al presupuesto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Obtiene la lista de precios asociada al presupuesto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priceList() {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    /**
     * Obtiene la tienda asociada al presupuesto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store() {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
