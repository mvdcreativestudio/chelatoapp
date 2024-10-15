<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'description', 'image_url', 'unit_of_measure'];

  /**
   * Obtiene la tienda a la que pertenece la materia prima.
   *
   * @return BelongsToMany
  */
  public function stores()
  {
      return $this->belongsToMany(Store::class, 'raw_material_store', 'raw_material_id', 'store_id')
                  ->withPivot('stock');
  }


  /**
   * Obtiene las ordenes de compra asociadas a la materia prima.
   *
   * @return BelongsToMany
  */
  public function supplierOrders(): BelongsToMany
  {
    return $this->belongsToMany(SupplierOrder::class, 'supplier_order_raw_material')
        ->withPivot('quantity')
        ->withTimestamps();
  }


  /**
   * Relación con las recetas.
   *
   * @return HasMany
  */
  public function recipes(): HasMany
  {
    return $this->hasMany(Recipe::class);
  }
}
