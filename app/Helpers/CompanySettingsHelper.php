<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use App\Models\CompanySettings;

class CompanySettingsHelper
{
    // Consultar con caché (se almacena y actualiza cada hora)
    // /**
    //  * Obtener si los reportes deben incluir impuestos.
    //  *
    //  * @return bool
    //  */
    // public static function shouldIncludeTaxes(): bool
    // {
    //     return Cache::remember('reports_include_taxes', 3600, function () {
    //         $setting = CompanySettings::first();
    //         return $setting ? (bool) $setting->reports_include_taxes : true;
    //     });
    // }

    // Consultar sin caché (cada vez que se consulta se mide este dato)
    /**
     * Obtener si los reportes deben incluir impuestos.
     *
     * @return bool
     */
    public static function shouldIncludeTaxes(): bool
    {
        $setting = CompanySettings::first();
        return $setting ? (bool) $setting->reports_include_taxes : true;
    }
}
