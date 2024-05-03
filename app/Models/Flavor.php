<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flavor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_flavor')->withTimestamps();
    }

}
