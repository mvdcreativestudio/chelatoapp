<?php

namespace App\Repositories;

use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class StoreRepository
{
  /**
   * Devuelve todas las tiendas.
   *
   * @return Collection
  */
  public function getAll(): Collection
  {
      if (auth()->user()->can('view_all_stores')) {
        return Store::withCount('users')->with('phoneNumber')->get();
      } else {
        return Store::where('id', auth()->user()->store_id)->withCount('users')->with('phoneNumber')->get();
      }
  }

  /**
   * Crea una nueva tienda con los datos proporcionados.
   *
   * @param array $data
   * @return Store
  */
  public function create(array $data): Store
  {
      return Store::create($data);
  }

  /**
   * Actualiza una tienda existente con los datos proporcionados.
   *
   * @param Store $store
   * @param array $data
   * @return Store
  */
  public function update(Store $store, array $data): Store
  {
      $store->update($data);
      return $store;
  }

  /**
   * Cambia el estado de la tienda.
   *
   * @param Store $store
   * @return RedirectResponse
  */
  public function toggleStoreStatus(Store $store): ?bool
  {
    try {

        $store->status = !$store->status;
        $store->save();
        return true;

    } catch (\Exception $e) {
        return false;
    }
  }


  /**
   * Cambia el abierto/cerrado de la tienda.
   *
   * @param $id
   * @return bool
  */
  public function toggleStoreStatusClosed($id): ?bool
  {
      try {
          $store = Store::findOrFail($id);
          $store->closed = !$store->closed;
          $store->manual_override_at = now();
          $store->save();
          return true;
      } catch (\Exception $e) {
          return false;
      }
  }



  /**
   * Elimina una tienda de la base de datos.
   *
   * @param Store $store
   * @return bool|null
  */
  public function delete(Store $store): ?bool
  {
      return $store->delete();
  }

  /**
   * Devuelve usuarios que no están asociados a ninguna tienda.
   *
   * @return Collection|User[]
  */
  public function getUnassociatedUsers(): Collection
  {
      return User::whereNull('store_id')->get();
  }

  /**
   * Asocia un usuario a una tienda.
   *
   * @param Store $store
   * @param int $userId
   * @return Store
  */
  public function associateUser(Store $store, int $userId): Store
  {
      $user = User::findOrFail($userId);
      $user->store_id = $store->id;
      $user->save();

      return $store;
  }

  /**
   * Desasocia un usuario de una tienda.
   *
   * @param Store $store
   * @param int $userId
   * @return Store
  */
  public function disassociateUser(Store $store, int $userId): Store
  {
      $user = User::findOrFail($userId);
      $user->store_id = null;
      $user->save();

      return $store;
  }

  /**
   * Guarda los horarios de la tienda.
   *
   * @param Store $store
   * @param array $hoursData
   * @return Store
  */
  public function saveStoreHours(Store $store, array $hoursData): Store
{
    foreach ($hoursData as $day => $data) {
        if (isset($data['open_all_day']) && $data['open_all_day']) {
            $open = '00:00';
            $close = '23:59';
        } else {
            $open = isset($data['open']) ? $this->formatTime($data['open']) : null;
            $close = isset($data['close']) ? $this->formatTime($data['close']) : null;

            if ($open === null && $close === null) {
                continue;
            }
        }

        $store->storeHours()->updateOrCreate(
            ['day' => $day, 'store_id' => $store->id],
            [
                'open' => $open,
                'close' => $close,
                'open_all_day' => isset($data['open_all_day']) && $data['open_all_day']
            ]
        );
    }

    return $store;
}



  /**
   * Formatea el tiempo para asegurar que el formato sea HH:MM:SS.
   * Si no se proporciona segundos, añade ':00'.
   * Si el tiempo está vacío, devuelve null.
   *
   * @param string|null $time
   * @return string|null
  */
  protected function formatTime(?string $time): ?string
  {
      if (empty($time)) {
          return null;
      }

      $parts = explode(':', $time);
      if (count($parts) < 3) {
          return $time . ':00';
      }
      return $time;
  }

  /**
   * Obtiene las tiendas con su estado.
   *
   * @return Collection
  */
  public function getStoresWithStatus(): Collection
  {
      $stores = Store::with('storeHours')->get();
      $now = Carbon::now()->format('H:i:s');
      $currentDay = ucfirst(Carbon::now()->isoFormat('dddd'));

      foreach ($stores as $store) {
          if ($store->closed) {
              $store->status = 'Cerrada';
              continue;
          }

          $store->status = 'Cerrada';
          $storeOpenForToday = false;

          if (!$store->closed) {
              foreach ($store->storeHours as $hours) {
                  if ($hours->day === $currentDay && $hours->open_all_day) {
                      $store->status = 'Abierta';
                      $storeOpenForToday = true;
                      break;
                  }

                  if ($hours->day === $currentDay) {
                      if ($now >= $hours->open && $now <= $hours->close) {
                          $store->status = 'Abierta';
                          $storeOpenForToday = true;
                          break;
                      }
                  }

                  if (!$storeOpenForToday && $hours->day === $currentDay) {
                      $store->status = 'Cerrada';
                  }
              }
          }
      }

      return $stores;
  }

  /**
   * Cambia el estado de la facturación automática de la tienda.
   *
   * @param Store $store
   * @return bool
  */
  public function toggleAutomaticBilling(Store $store): bool
  {
      try {
          $store->automatic_billing = !$store->automatic_billing;
          $store->save();
          return true;
      } catch (\Exception $e) {
          return false;
      }
  }
}
