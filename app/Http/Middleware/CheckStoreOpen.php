<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\StoreHours;
use Carbon\Carbon;

class CheckStoreOpen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $storeData = $request->session()->get('store');

        if (!$storeData) {
          return redirect()->route('home')->with('error', 'La tienda está cerrada en este momento.');
        }

        $store = Store::find($storeData['id']);

        if (!$store) {
          return redirect()->route('home')->with('error', 'La tienda está cerrada en este momento.');
        }

        // Verificar si la tienda está cerrada manualmente
        if ($store->closed) {
          return redirect()->route('home')->with('error', 'La tienda está cerrada en este momento.');
        }

        $now = Carbon::now();
        $day = $this->getDayName($now->dayOfWeek); // Obtener el nombre del día en español

        $storeHour = StoreHours::where('store_id', $store->id)
                              ->where('day', $day)
                              ->first();

        if ($storeHour) {
            // Si está marcado para estar abierto todo el día, permitir la solicitud
            if ($storeHour->open_all_day) {
                return $next($request);
            }

            $openTime = Carbon::createFromTimeString($storeHour->open);
            $closeTime = Carbon::createFromTimeString($storeHour->close);

            if ($now->between($openTime, $closeTime)) {
                return $next($request);
            }
        }

        session()->flash('store_closed_error', 'La tienda está cerrada en este momento.');
        return redirect()->route('shop')->with('error', 'La tienda está cerrada en este momento.');
    }

    /**
     * Get the Spanish name of the day.
     *
     * @param int $dayOfWeek
     * @return string
     */
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
}
