<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadTask extends Model
{
    use HasFactory;

    protected $fillable = ['leads_id', 'description', 'status','priority','due_date'];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'leads_id', 'id');
    }
}
