<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address', 'city', 'state', 'country', 'email', 'doc_type', 'doc_number', 'default_payment_method', 'store_id'];

    /**
     * Obtiene la tienda a la que pertenece el proveedor.}
     *
     * @return BelongsTo
    */
    public function store(): BelongsTo
    {
      return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene las ordenes de compra asociadas al proveedor.
     *
     * @return HasMany
    */
    public function orders(): HasMany
    {
      return $this->hasMany(SupplierOrder::class);
    }

    /**
     * Obtiene las cuentas corrientes asociadas al proveedor.
     *
     * @return HasMany
    */
    public function currentAccount(): HasMany
    {
      return $this->hasMany(CurrentAccount::class);
    }
}
