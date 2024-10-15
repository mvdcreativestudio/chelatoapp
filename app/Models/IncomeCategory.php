<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'income_name',
        'income_description'
    ];

    // Relación con Income
    public function incomes()
    {
        return $this->hasMany(Income::class);
    }
}
