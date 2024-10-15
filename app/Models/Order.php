<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;


class Order extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'time', 'origin', 'client_id',
      'store_id', 'products', 'subtotal', 'tax', 'shipping', 'coupon_id',
      'coupon_amount', 'discount', 'total', 'payment_status', 'shipping_status',
      'payment_method', 'shipping_method', 'estimate_id', 'shipping_id', 'uuid', 'is_billed', 'doc_type', 'document', 'cash_register_log_id'];

    /**
     * The "booted" method of the model.
     *
     * Genera un UUID cuando se crea una nueva orden.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Generar UUID cuando se crea una orden
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::uuid4()->toString();
            }
        });
    }

    /**
     * Obtiene el cliente al que pertenece el pedido.
     *
     * @return BelongsTo
    */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtiene los productos asociados al pedido.
     *
     * @return BelongsToMany
    */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_products')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    /**
     * Define la relación con el modelo CashRegisterLog.
     *
     * @return BelongsTo
     */
    public function cashRegisterLog(): BelongsTo
    {
        return $this->belongsTo(CashRegisterLog::class, 'cash_register_log_id');
    }

    /**
     * Obtiene la tienda a la que pertenece el pedido.
     *
     * @return BelongsTo
    */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene el cupón asociado al pedido.
     *
     * @return BelongsTo
    */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Obtiene el nombre de la clave de ruta para el modelo.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Obtiene los cambios de estado de la orden.
     *
     * @return HasMany
    */
    public function statusChanges()
    {
      return $this->hasMany(OrderStatusChange::class);
    }

    /**
     * Obtiene los recibos asociados a la orden.
     *
     * @return HasMany
    */
    public function invoices(): HasMany
    {
        return $this->hasMany(CFE::class);
    }

    /**
     * Setea el valor redondeado de subtotal.
     * @param float $value
     * @return void
    */
    public function setSubtotalAttribute($value)
    {
        $this->attributes['subtotal'] = round($value, 2);
    }

    /**
     * Setea el valor redondeado del total.
     * @param float $value
     * @return void
    */
    public function setTotalAttribute($value)
    {
        $this->attributes['total'] = round($value, 2);
    }

    /**
     * Setea el valor redondeado del impuesto.
     * @param float $value
     * @return void
    */
    public function setTaxAttribute($value)
    {
        $this->attributes['tax'] = round($value, 2);
    }

    /**
     * Setea el valor redondeado del envío.
     * @param float $value
     * @return void
    */
    public function setShippingAttribute($value)
    {
        $this->attributes['shipping'] = round($value, 2);
    }

    /**
     * Setea el valor redondeado del descuento.
     * @param float $value
     * @return void
    */
    public function setDiscountAttribute($value)
    {
        $this->attributes['discount'] = round($value, 2);
    }
}
