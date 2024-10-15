<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
  use HasFactory;

  protected $fillable = [
    'product_id',
    'flavor_id',
    'raw_material_id',
    'quantity',
    'used_flavor_id'
  ];

  /**
   * Relación con el producto.
   *
   * @return BelongsTo
  */
  public function product(): BelongsTo
  {
    return $this->belongsTo(Product::class);
  }

  /**
   * Relación con la materia prima.
   *
   * @return BelongsTo
  */
  public function rawMaterial(): BelongsTo
  {
    return $this->belongsTo(RawMaterial::class);
  }

  /**
   * Relación con el sabor.
   *
   * @return BelongsTo
  */
  public function flavor(): BelongsTo
  {
    return $this->belongsTo(Flavor::class);
  }

  /**
   * Relación con el sabor usado.
   *
   * @return BelongsTo
  */
  public function usedFlavor(): BelongsTo
  {
    return $this->belongsTo(Flavor::class, 'used_flavor_id');
  }

  /**
   * Relación con las producciones.
   *
   * @return HasMany
   */
  public function productions(): HasMany
  {
      return $this->hasMany(Production::class);
  }
}
