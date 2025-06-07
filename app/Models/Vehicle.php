<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    // Tabla asociada
    protected $table = 'vehicles';

    // Campos que se pueden llenar de forma masiva
    protected $fillable = [
        'number',
        'brand',
        'plate',
        'capacity',
        'applus_date',
    ];
}
