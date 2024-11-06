<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosDevice extends Model
{
    protected $fillable = ['pos_provider_id', 'identifier', 'user', 'cash_register', 'description'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PosProvider::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');  
    }
    
}
