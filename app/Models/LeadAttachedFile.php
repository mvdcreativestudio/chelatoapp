<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadAttachedFile extends Model
{
    use HasFactory;

    protected $fillable = ['lead_id', 'file'];

    /**
     * Relación con el modelo Lead.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
