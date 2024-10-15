<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\EcommerceSetting;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Usando Closure
        View::composer('*', function ($view) {
            $settings = EcommerceSetting::first();
            $view->with('settings', $settings);
        });
    }

}
