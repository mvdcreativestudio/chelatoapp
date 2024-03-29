<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySettings extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'city', 'state', 'country', 'phone', 'email', 'website', 'logo', 'rut'];
}
