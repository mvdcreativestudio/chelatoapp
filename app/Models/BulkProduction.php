<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BulkProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'formula_id',
        'quantity_produced',
        'production_date',
        'quantity_used',
        'user_id'
    ];

    public function formula()
    {
        return $this->belongsTo(Formula::class);
    }


    public function Product(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function batches()
    {
        return $this->hasMany(BulkProductionBatch::class, 'bulk_productions_id');
    }

    public function getUniqueIdentifier()
    {
        return $this->id . '-' . Str::slug($this->name);
    }

    public function packaging(): HasMany
    {
        return $this->hasMany(Packaging::class);
    }
}
