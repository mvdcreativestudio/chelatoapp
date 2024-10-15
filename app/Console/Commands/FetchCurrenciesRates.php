<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\CurrencyRate;
use App\Models\CurrencyRateHistory;

class FetchCurrenciesRates extends Command
{
    protected $signature = 'fetch:currencies-rates';
    protected $description = 'Obtiene y actualiza las tasas de cambio de las monedas.';

    public function handle()
    {
        try {
            $response = Http::get('https://broucurrenciesapi-cold-shadow-6325.fly.dev/currencies');

            if ($response->successful()) {
                $currencies = $response->json();

                foreach ($currencies as $currency) {
                    $buyRate = $currency['compra'] === '-' ? null : str_replace(',', '.', $currency['compra']);
                    $sellRate = $currency['venta'] === '-' ? null : str_replace(',', '.', $currency['venta']);

                    // Actualiza o crea la divisa base
                    $currencyRate = CurrencyRate::firstOrCreate(
                        ['name' => $currency['moneda']]
                    );

                    // Agrega la tasa de cambio diaria a la tabla histórica
                    CurrencyRateHistory::updateOrCreate(
                        ['currency_rate_id' => $currencyRate->id, 'date' => now()->toDateString()],
                        [
                            'buy' => $buyRate,
                            'sell' => $sellRate,
                        ]
                    );
                }

                $this->info('Currency rates fetched and saved successfully.');
            } else {
                $this->error('Failed to fetch data from the API.');
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }

        return 0;
    }
}
