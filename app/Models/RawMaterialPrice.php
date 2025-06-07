<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterialPrice extends Model
{
  use HasFactory;

  protected $fillable = [
    'name', 
    'raw_material_id', 
    'currency',
    'price'
    ];


    public function rawMaterial()
    {
        return $this->belongsToMany(RawMaterial::class);
    }
}
