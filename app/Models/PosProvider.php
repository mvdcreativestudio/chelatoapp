<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosProvider extends Model
{
    protected $fillable = ['name', 'api_url', 'requires_token'];

    public function devices(): HasMany
    {
        return $this->hasMany(PosDevice::class);
    }
}
