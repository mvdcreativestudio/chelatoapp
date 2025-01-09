<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Store extends Model
{
    use HasSlug;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'email',
        'rut',
        'ecommerce',
        'status',
        'slug',
        'closed',
        'manual_override_at',
        'automatic_billing',
        'invoices_enabled',
        'pymo_user',
        'pymo_password',
        'pymo_branch_office',
        'accepts_peya_envios',
        'peya_envios_key',
    ];

    /**
     * Obtiene las materias primas asociadas a la tienda.
     *
     * @return BelongsToMany
     */
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class, 'raw_material_store', 'store_id', 'raw_material_id')
                    ->withPivot('stock');
    }


    /**
     * Obtiene los usuarios asociados a la tienda.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Obtiene los productos asociados a la tienda.
     *
     * @return HasMany
    */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'store_id');  // Asegúrate de que 'store_id' sea la clave foránea correcta en la tabla de órdenes
    }

    /**
     * Obtiene los numeros de telefono asociados a la tienda.
     *
     * @return HasOne
    */
    public function phoneNumber(): HasOne
    {
        return $this->hasOne(PhoneNumber::class);
    }

    /**
     * Obtiene la cuenta de Mercado Pago asociada a la tienda.
     *
     * @return HasOne
    */
    public function mercadoPagoAccount(): HasMany
    {
        return $this->hasMany(MercadoPagoAccount::class);
    }

    /**
     * Obtiene la cuenta de Mercado Pago asociada a la tienda.
     *
     * @return HasOne
    */
    public function mercadoPagoAccountStore(): HasOne
    {
        return $this->hasOne(MercadoPagoAccountStore::class);
    }

    /**
     * Genera un slug para la tienda automáticamente.
     *
     * @return SlugOptions
    */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Obtiene las horas de apertura y cierre de la tienda.
     *
     * @return HasMany
     */
    public function storeHours(): HasMany
    {
        return $this->hasMany(StoreHours::class);
    }


    /**
     * Obtiene las cajas registradoras asociadas a la tienda.
     *
     * @return HasMany
     */
    public function cashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class);
    }

    /**
     * Obtiene los recibos asociados a la tienda.
     *
     * @return HasMany
    */
    public function invoices(): HasMany
    {
        return $this->hasMany(CFE::class);
    }

    /**
     * Obtiene los POS vinculados a la tienda.
     * 
     * @return BelongsTo
     */
    public function posProvider()
    {
        return $this->belongsTo(PosProvider::class, 'pos_provider_id');
    }

    /**
     * Obtiene la información de integración con el POS.
     * 
     * @return HasOne
     */
    public function posIntegrationInfo()
    {
        return $this->hasOne(PosIntegrationStoreInfo::class, 'store_id');
    }
    

    /**
     * Obtiene los dispositivos POS asociados a la tienda.
     * 
     * @return HasMany
     */
    public function posDevices()
    {
        // Primero obtenemos la integración POS de la tienda, luego los dispositivos asociados
        return $this->hasManyThrough(PosDevice::class, PosIntegrationStoreInfo::class, 'store_id', 'pos_provider_id', 'id', 'pos_provider_id');
    }
    

    /**
     * Obtiene la configuración de envío de correos electrónicos de la tienda.
     * 
     * @return HasOne
     */

    public function emailConfig()
    {
        return $this->hasOne(StoresEmailConfig::class, 'store_id');
    }
}
