<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Packaging extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_production_id',
        'quantity_packaged',
        'package_id',
        'packaging_date',
    ];

    public function bulkProduction(): BelongsTo
    {
        return $this->belongsTo(BulkProduction::class, 'bulk_production_id');
    }

    public function finalProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'final_product_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
