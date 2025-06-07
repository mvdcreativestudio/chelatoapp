<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkProductionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_productions_id',
        'batch_id',
        'quantity_used'
    ];

    public function bulkProduction()
    {
        return $this->belongsTo(BulkProduction::class, 'bulk_productions_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'product_id');
    }
}
