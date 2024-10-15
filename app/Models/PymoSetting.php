<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PymoSetting extends Model
{
    use HasFactory;

    protected $fillable = ['settingKey', 'settingValue'];
}
