<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreHours extends Model
{
    protected $fillable = [
        'store_id',
        'day',
        'open',
        'close',
        'closed',
        'open_all_day',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

