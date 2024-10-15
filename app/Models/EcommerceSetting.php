<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
      'enable_coupons',
      'currency',
      'currency_symbol',
      'decimal_separator',
      'thousands_separator',
    ];
}
