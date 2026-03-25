<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingProvider extends Model
{
    protected $fillable = [
        'name',
        'code',
        'base_url',
        'description',
    ];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }
}
