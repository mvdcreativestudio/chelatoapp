<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CashRegisterLogRepository;
use App\Repositories\ClientRepository;
use App\Models\CompanySettings;

class CompanySettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registra companySettings para ser utilizado globalmente
        $this->app->singleton('companySettings', function ($app) {
            if (\Schema::hasTable('company_settings')) {
                return CompanySettings::first();
            } else {
                return null;
            }
        });

        // Inyecta el companySettings al CashRegisterLogRepository
        $this->app->bind(CashRegisterLogRepository::class, function ($app) {
            $companySettings = $app->make('companySettings');
            return new CashRegisterLogRepository($companySettings);
        });

        // Inyecta el companySettings al ClientRepository
        $this->app->bind(ClientRepository::class, function ($app) {
            $companySettings = $app->make('companySettings');
            return new ClientRepository($companySettings);
        });
    }

    public function boot()
    {
        if (\Schema::hasTable('company_settings')) {
            view()->share('companySettings', $this->app->make('companySettings'));
        }
    }
}
