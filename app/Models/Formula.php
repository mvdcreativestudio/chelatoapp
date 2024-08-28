<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formula extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'final_product_id',
    ];

    public function formulaRawMaterials(): HasMany
    {
        return $this->hasMany(FormulaRawMaterial::class);
    }

    public function bulkProductions(): HasMany
    {
        return $this->hasMany(BulkProduction::class);
    }
}
