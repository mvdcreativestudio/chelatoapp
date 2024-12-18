<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'drivers';

    protected $fillable = [
        'name',
        'last_name',
        'document',
        'address',
        'phone',
        'license_date',
        'is_active',
        'health_date',
    ];

}
