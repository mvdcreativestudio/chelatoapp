<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catalogue extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'display_catalogue',
        'email',
        'phone_code',
        'phone',
        'logo',
        'show_whatsapp_button',
        'header_image',
        'allow_share',
    ];

    /**
     * Obtiene la tienda que posee el catalogo
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

}
