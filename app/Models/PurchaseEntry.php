<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'entry_date',
        'purchase_order_items_id'
    ];

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_items_id');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class, 'purchase_entries_id');
    }

}
