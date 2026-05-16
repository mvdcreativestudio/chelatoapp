<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'currency',
    ];

    /**
     * Tienda a la que pertenece la lista.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Productos asociados a la lista, con su precio especial.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'price_list_products')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    /**
     * Clientes asignados a la lista.
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_price_lists')
                    ->withTimestamps();
    }
}
