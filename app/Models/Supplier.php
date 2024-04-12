<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address', 'city', 'state', 'country', 'email', 'doc_type', 'doc_number', 'store_id'];

    /**
     * Obtiene la tienda a la que pertenece el proveedor.}
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function store()
    {
      return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene las ordenes de compra asociadas al proveedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function supplierOrders()
    {
      return $this->hasMany(SupplierOrder::class);
    }
}
