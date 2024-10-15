<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySettings extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'city', 'state', 'country', 'phone', 'email', 'website', 'facebook', 'instagram', 'linkedin', 'youtube', 'twitter',  'logo_white', 'logo_black', 'rut', 'allow_registration', 'clients_has_store', 'categories_has_store'];
}
