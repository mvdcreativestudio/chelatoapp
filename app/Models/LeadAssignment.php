<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadAssignment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'lead_id',
        'user_id',
    ];

    /**
     * Retorna la relación con el lead.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Retorna la relación con el usuario. 
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
