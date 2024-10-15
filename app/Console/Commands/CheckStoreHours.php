<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;
use App\Models\StoreHours;
use Carbon\Carbon;

class CheckStoreHours extends Command
{
    protected $signature = 'stores:check-hours';
    protected $description = 'Check store hours and update their open/closed status';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();
        $day = $this->getDayName($now->dayOfWeek);
        $currentTime = $now->format('H:i'); // Formato H:i para comparar horas y minutos

        $stores = Store::all();

        foreach ($stores as $store) {
            $storeHour = StoreHours::where('store_id', $store->id)
                ->where('day', $day)
                ->first();

            if ($storeHour) {
                if ($storeHour->open_all_day) {
                    // Abierto todo el día
                    if ($store->closed && $this->shouldOverrideManualChange($store, 'open', $currentTime)) {
                        $store->closed = 0; // Abrir
                        $store->manual_override_at = null; // Limpiar override manual
                        $store->save();
                        $this->info("Store ID {$store->id} opened for the whole day");
                    }
                } else {
                    $openTime = Carbon::createFromTimeString($storeHour->open)->format('H:i');
                    $closeTime = Carbon::createFromTimeString($storeHour->close)->format('H:i');

                    // Información para depuración
                    $this->info("Store ID {$store->id} - Current Time: {$currentTime}, Open Time: {$openTime}, Close Time: {$closeTime}");

                    if ($currentTime >= $openTime && $currentTime < $closeTime) {
                        if ($store->closed && $this->shouldOverrideManualChange($store, 'open', $currentTime, $openTime)) {
                            $store->closed = 0; // Abrir
                            $store->manual_override_at = null; // Limpiar override manual
                            $store->save();
                            $this->info("Store ID {$store->id} opened at {$currentTime}");
                        }
                    } else {
                        if (!$store->closed && $this->shouldOverrideManualChange($store, 'close', $currentTime, $closeTime)) {
                            $store->closed = 1; // Cerrar
                            $store->manual_override_at = null; // Limpiar override manual
                            $store->save();
                            $this->info("Store ID {$store->id} closed at {$currentTime}");
                        }
                    }
                }
            } else {
                $this->info("No store hours found for Store ID {$store->id} on {$day}");
            }
        }

        $this->info('Store hours checked and statuses updated.');
    }

    private function getDayName(int $dayOfWeek): string
    {
        $days = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];

        return $days[$dayOfWeek];
    }

    private function shouldOverrideManualChange($store, $action, $currentTime, $nextTransitionTime = null)
    {
        // Si no hay override manual, permitir la acción
        if (!$store->manual_override_at) {
            return true;
        }

        $manualOverrideDay = Carbon::parse($store->manual_override_at)->startOfDay();
        $currentDay = Carbon::now()->startOfDay();

        // Si el override manual se hizo en un día anterior, permitir la acción
        if ($manualOverrideDay->lt($currentDay)) {
            return true;
        }

        // Determinar el tipo de acción de override manual
        $manualOverrideTime = Carbon::parse($store->manual_override_at)->format('H:i');

        if ($action === 'open' && $store->closed) {
            // Verificar si la empresa fue cerrada manualmente y debe abrirse ahora
            return $currentTime >= $nextTransitionTime && $manualOverrideTime < $nextTransitionTime;
        } elseif ($action === 'close' && !$store->closed) {
            // Verificar si la empresa fue abierta manualmente y debe cerrarse ahora
            return $currentTime >= $nextTransitionTime && $manualOverrideTime < $nextTransitionTime;
        }

        return false;
    }
}
