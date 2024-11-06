<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'invoice_number',
        'invoice_type',
        'currency',
        'exchange_rate',
        'due_date',
        'total_amount',
        'cfe_id',
        'payment_status'
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplierInvoicePayments(): HasMany
    {
        return $this->hasMany(SupplierInvoicePayment::class);
    }

    public function purchaseEntries(): HasMany
    {
        return $this->hasMany(PurchaseEntry::class);
    }
}
