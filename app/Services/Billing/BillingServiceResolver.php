<?php

namespace App\Services\Billing;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class BillingServiceResolver
{
    protected array $services;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * @param  Store|null  $store  Tienda explícita (p. ej. integraciones admin). Si es null, usa la tienda del usuario autenticado.
     */
    public function resolve(?Store $store = null): BillingServiceInterface
    {
        $store = $store ?? Auth::user()?->store;

        if (!$store) {
            Log::error('No se pudo determinar la tienda para el servicio de facturación.');
            throw new InvalidArgumentException('No se pudo determinar la tienda para el servicio de facturación.');
        }

        $store->loadMissing('billingProvider');

        if (!$store->billingProvider) {
            Log::error('La tienda no tiene un proveedor de facturación definido.', ['store_id' => $store->id]);
            throw new InvalidArgumentException('La tienda no tiene un proveedor de facturación definido.');
        }

        $code = strtolower((string) $store->billingProvider->code);

        if (! isset($this->services[$code])) {
            throw new InvalidArgumentException("Proveedor de facturación no soportado: {$code}");
        }

        return $this->services[$code];
    }
}
