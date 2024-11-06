<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'quantity',
        'production_date',
        'expiration_date',
        'purchase_entries_id'
    ];

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class, 'purchase_entries_id');
    }

  
    public function BulkProductionBatch(): HasMany
    {
      return $this->hasMany(BulkProductionBatch::class);
    }
}
