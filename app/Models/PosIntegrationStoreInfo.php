<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosIntegrationStoreInfo extends Model
{
    protected $table = 'pos_integrations_store_info';

    protected $fillable = [
        'store_id',
        'pos_provider_id',
        'company',
        'branch',
        'system_id',
        'created_at',
        'updated_at',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function posProvider()
    {
        return $this->belongsTo(PosProvider::class, 'pos_provider_id');
    }

    public function posDevices()
    {
        return $this->hasMany(PosDevice::class, 'pos_integration_info_id');
    }

}
