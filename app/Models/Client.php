<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'name', 'lastname', 'branch', 'type', 'rut', 'ci', 'passport', 'doc_ext',
    'address', 'city', 'state', 'country', 'phone', 'email', 'tax_rate_id', 'website', 'logo', 'doc_type', 'document', 'company_name'];


    /**
     * Obtiene las ordenes del cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtiene el conteo de ordenes del cliente.
     *
     * @return int
     */
    public function ordersCount(): int
    {
        return $this->orders()->count();
    }

    /**
     * Obtiene las ordenes del cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posorders()
    {
        return $this->hasMany(PosOrder::class);
    }

    /**
     * Obtiene las ordenes del cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function currentAccount()
    {
        return $this->hasOne(CurrentAccount::class);
    }

    /**
     * Obtiene las listas de precios del cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function priceLists()
    {
        return $this->belongsToMany(PriceList::class, 'client_price_lists');
    }

    /**
     * Obtiene el impuesto asociado al cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }

}
