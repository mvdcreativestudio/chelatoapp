<?php

namespace App\Repositories;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class CouponRepository
{
  /**
   * Obtiene todos los cupones.
   *
   * @return Collection
  */
  public function getAllCoupons(): Collection
  {
    return Coupon::all();
  }

  /**
   * Obtiene un cupón por su ID.
   *
   * @param int $id
   * @return Coupon|null
  */
  public function getCouponById(int $id): ?array
  {
      $coupon = Coupon::with(['excludedProducts:id', 'excludedCategories:id'])->find($id);

      if (!$coupon) {
          return null;
      }

      return [
          'id' => $coupon->id,
          'code' => $coupon->code,
          'type' => $coupon->type,
          'amount' => $coupon->amount,
          'init_date' => $coupon->init_date ? Carbon::parse($coupon->init_date)->format('Y-m-d') : null,
          'due_date' => $coupon->due_date ? Carbon::parse($coupon->due_date)->format('Y-m-d') : null,
          'excluded_products' => $coupon->excludedProducts->pluck('id')->toArray(),
          'excluded_categories' => $coupon->excludedCategories->pluck('id')->toArray(),
          'single_use' => $coupon->single_use,
      ];
  }

  public function getCouponByName(string $code): ?array
  {
      $coupon = Coupon::with(['excludedProducts:id', 'excludedCategories:id'])
                      ->where('code', $code)
                      ->first();

      if (!$coupon) {
          return null;
      }

      return [
          'id' => $coupon->id,
          'code' => $coupon->code,
          'type' => $coupon->type,
          'amount' => $coupon->amount,
          'init_date' => $coupon->init_date ? Carbon::parse($coupon->init_date)->format('Y-m-d') : null,
          'due_date' => $coupon->due_date ? Carbon::parse($coupon->due_date)->format('Y-m-d') : null,
          'excluded_products' => $coupon->excludedProducts->pluck('id')->toArray(),  // ✅ Ahora trae los excluidos
          'excluded_categories' => $coupon->excludedCategories->pluck('id')->toArray(),
      ];
  }



  /**
   * Crea un nuevo cupón y guarda las exclusiones.
   *
   * @param array $data
   * @return array
   */
  public function createCoupon(array $data): array
  {

    try {
      $currentDate = date('Y-m-d');
      $dueDate = isset($data['due_date']) ? date('Y-m-d', strtotime($data['due_date'])) : null;
      $status = ($dueDate && $dueDate <= $currentDate) ? 0 : 1;

      $singleUse = filter_var($data['single_use'] ?? 0, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

      Log::info('Valores a guardar en el cupón:', [
          'code' => $data['code'],
          'type' => $data['type'],
          'amount' => $data['amount'],
          'init_date' => $data['init_date'] ?? null,
          'due_date' => $data['due_date'] ?? null,
          'creator_id' => auth()->id(),
          'status' => $status,
          'single_use' => $singleUse
      ]);

      Log::info('Tipo y valor de single_use:', [
        'raw' => $data['single_use'],
        'tipo' => gettype($data['single_use']),
        'comparación == 1' => $data['single_use'] == '1',
        'comparación === 1' => $data['single_use'] === '1',
      ]);


      $coupon = Coupon::create([
          'code' => $data['code'],
          'type' => $data['type'],
          'amount' => $data['amount'],
          'init_date' => $data['init_date'] ?? null,
          'due_date' => $data['due_date'] ?? null,
          'creator_id' => auth()->id(),
          'status' => $status,
          'single_use' => $singleUse,
      ]);

      Log::info('Cupón creado con éxito:', ['coupon_id' => $coupon->id]);

      // ✅ Guardar exclusiones de productos
      if (!empty($data['excluded_products']) && is_array($data['excluded_products'])) {
          Log::info('Productos excluidos recibidos:', $data['excluded_products']);
          $coupon->excludedProducts()->sync($data['excluded_products']);
          Log::info('Productos excluidos guardados correctamente.');
      } else {
          Log::info('No se enviaron productos excluidos.');
      }

      // ✅ Guardar exclusiones de categorías
      if (!empty($data['excluded_categories']) && is_array($data['excluded_categories'])) {
          Log::info('Categorías excluidas recibidas:', $data['excluded_categories']);
          $coupon->excludedCategories()->sync($data['excluded_categories']);
          Log::info('Categorías excluidas guardadas correctamente.');
      } else {
          Log::info('No se enviaron categorías excluidas.');
      }

      return ['success' => true, 'message' => 'Cupón creado con éxito'];
    } catch (\Exception $e) {
        Log::error('Error al crear el cupón: ' . $e->getMessage());
        return ['success' => false, 'message' => 'No se pudo crear el cupón: ' . $e->getMessage()];
    }

  }




  /**
   * Actualiza un cupón y sus exclusiones.
   *
   * @param int $id
   * @param array $data
   * @return array
   */
  public function updateCoupon(int $id, array $data): array
  {
      $coupon = Coupon::find($id);

      if (!$coupon) {
          return ['success' => false, 'message' => 'El cupón no existe'];
      }

      try {
          $currentDate = date('Y-m-d');
          $initDate = isset($data['init_date']) ? date('Y-m-d', strtotime($data['init_date'])) : null;
          $dueDate = isset($data['due_date']) ? date('Y-m-d', strtotime($data['due_date'])) : null;
          $status = ($dueDate && $dueDate <= $currentDate) ? 0 : 1;

          $coupon->update([
              'code' => $data['code'],
              'type' => $data['type'],
              'amount' => $data['amount'],
              'init_date' => $initDate,
              'due_date' => $dueDate,
              'creator_id' => auth()->id(),
              'status' => $status,
              'single_use' => $data['single_use']
          ]);

          Log::info('Productos excluidos recibidos:', $data['excluded_products'] ?? []);
          Log::info('Categorías excluidas recibidas:', $data['excluded_categories'] ?? []);

          // ✅ Actualizar exclusiones de productos
          if (isset($data['excluded_products']) && is_array($data['excluded_products'])) {
              $coupon->excludedProducts()->sync($data['excluded_products']);
          } else {
              $coupon->excludedProducts()->sync([]);
          }

          // ✅ Actualizar exclusiones de categorías
          if (isset($data['excluded_categories']) && is_array($data['excluded_categories'])) {
              $coupon->excludedCategories()->sync($data['excluded_categories']);
          } else {
              $coupon->excludedCategories()->sync([]);
          }

          return ['success' => true, 'message' => 'Cupón actualizado con éxito'];
      } catch (\Exception $e) {
          Log::error('Error al actualizar el cupón: ' . $e->getMessage());
          return ['success' => false, 'message' => 'No se pudo actualizar el cupón: ' . $e->getMessage()];
      }
  }




  /**
   * Elimina un cupón y sus exclusiones.
   *
   * @param int $id
   * @return array
   */
  public function deleteCoupon(int $id): array
  {
      $coupon = Coupon::find($id);

      if (!$coupon) {
          return ['success' => false, 'message' => 'El cupón no existe'];
      }

      try {
          // ✅ Eliminar las relaciones en las tablas pivot antes de eliminar el cupón
          $coupon->excludedProducts()->detach();
          $coupon->excludedCategories()->detach();

          $coupon->delete();

          return ['success' => true, 'message' => 'Cupón eliminado correctamente'];
      } catch (\Exception $e) {
          return ['success' => false, 'message' => 'No se pudo eliminar el cupón: ' . $e->getMessage()];
      }
  }



  /**
   * Elimina los cupones seleccionados por sus IDs.
   *
   * @param array $selectedIds
   * @return array
  */
  public function deleteSelectedCoupons(array $selectedIds): array
  {
    Coupon::whereIn('id', $selectedIds)->delete();
    return ['success' => true, 'message' => 'Cupones seleccionados eliminados correctamente'];
  }

  /**
   * Obtiene los datos para mostrar en la tabla de cupones.
   *
   * @return mixed
  */
  public function datatable(): mixed
  {
    $coupons = $this->getCouponsWithCreator();

    return Datatables::of($coupons)
      ->addColumn('creator_name', function($coupon){
          return $coupon->creator ? $coupon->creator->name : 'No registrado';
      })
      ->addColumn('action', function($coupon){
          return '<a href="'.route('coupons.edit', $coupon->id).'" class="btn btn-primary btn-sm">Editar</a>';
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  /**
   * Obtiene los cupones con su creador.
   *
   * @return Collection
  */
  public function getCouponsWithCreator(): Collection
  {
    return Coupon::with('creator')->get();
  }

  public function getAllProducts(): Collection
  {
    return Product::all();
  }

  public function getAllCategories(): Collection
  {
    return ProductCategory::all();
  }
}
