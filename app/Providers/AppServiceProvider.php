<?php

namespace App\Providers;

use App\Repositories\EmailNotificationsRepository;
use App\Repositories\StoresEmailConfigRepository;
use App\Services\Mail\EmailService;
use App\Services\Mail\BrevoMailer;
use App\Services\Mail\MailgunMailer;
use App\Services\Mail\MailProviderInterface;
use App\Services\Mail\NativeMailer;
use App\Services\POS\PosIntegrationInterface;
use App\Services\POS\ScanntechAuthService;
use App\Services\POS\ScanntechIntegrationService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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

        $this->app->singleton(MailProviderInterface::class, function ($app) {
            $provider = config('mail.provider'); // Configura esto en `config/mail.php`

            return match ($provider) {
                'mailgun' => new MailgunMailer(),
                'brevo' => new BrevoMailer(),
                default => new NativeMailer(),
            };
        });

        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService(
                $app->make(MailProviderInterface::class),
                $app->make(StoresEmailConfigRepository::class)
            );
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
                    (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : ''),
                ];
            }
            return [];
        });
    }
}
