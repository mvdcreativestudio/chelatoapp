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

  protected $fillable = [
    'name', 
    'description', 
    'status', // Agregar columna despues de description, debe ser un enum que permita 0,1,2.
    'image_url', 
    'unit_of_measure',
    'stock' // Agregar columna despué de unit_of_measure, debe ser un int.
    ];


  public function purchaseOrderItems(): HasMany
  {
      return $this->hasMany(PurchaseOrderItem::class);
  }

  public function batch(): HasMany
  {
      return $this->hasMany(Batch::class);
  }

  public function formulaRawMaterials(): HasMany
  {
      return $this->hasMany(FormulaRawMaterial::class);
  }

  public function purchaseEntryRawMaterial(): HasMany
  {
      return $this->hasMany(PurchaseEntry::class);
  }

  public function prices(): HasMany
  {
      return $this->hasMany(RawMaterialPrices::class);
  }
}
