<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'lastname', 'email', 'phone', 'user_id'];

    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Obtiene el usuario asociado al vendedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene las órdenes asociadas al vendedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relación con los clientes asociados al vendedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
