<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadCategories extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'order', 'color'];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'category_id');
    }
}
