<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadConversation extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'lead_conversations';

    protected $fillable = [
        'lead_id',
        'message',
        'user_id',
        'is_deleted'
    ];

    /**
     * Relación con el modelo Lead.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    /**
     * Relación con el modelo user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
