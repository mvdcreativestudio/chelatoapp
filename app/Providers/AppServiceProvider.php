<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EmailNotificationsRepository;
use App\Services\POS\PosIntegrationInterface;
use App\Services\POS\ScanntechIntegrationService;
use App\Services\POS\ScanntechAuthService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register()
  {
      $this->app->singleton(EmailNotificationsRepository::class, function ($app) {
          return new EmailNotificationsRepository();
      });

      $this->app->bind(PosIntegrationInterface::class, function ($app) {
        // Aquí puedes añadir lógica para elegir el POS, por ejemplo basándote en el cliente
        return new ScanntechIntegrationService($app->make(ScanntechAuthService::class));
      });
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
      if ($src !== null) {
        return [
          'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' :
                    (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
        ];
      }
      return [];
    });
  }
}
