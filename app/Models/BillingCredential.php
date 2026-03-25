<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingCredential extends Model
{
    protected $fillable = [
        'store_id',
        'user',
        'password',
        'branch_office',
        'has_special_caes',
        'tenant',
    ];

    protected $casts = [
        'has_special_caes' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function billingProvider()
    {
        return $this->belongsTo(BillingProvider::class);
    }
}
