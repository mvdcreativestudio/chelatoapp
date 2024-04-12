<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmniSetting extends Model
{
    use HasFactory;

    protected $fillable = ['setting_name', 'setting_value', 'setting_type'];
}
