<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Repositories\CouponRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
  /**
   * El repositorio de cupones.
   *
   * @var CouponRepository
  */
  protected CouponRepository $couponRepository;

  /**
   * Inyecta el repositorio en el controlador.
   *
   * @param CouponRepository $couponRepository
  */
  public function __construct(CouponRepository $couponRepository)
  {
    $this->middleware(['check_permission:access_coupons', 'user_has_store'])->only(
      [
        'index',
        'show',
        'store',
        'edit',
        'update',
        'destroy',
        'deleteSelected',
        'datatable'
      ]
    );

    $this->couponRepository = $couponRepository;
  }

  /**
   * Muestra una lista de cupones.
   *
   * @return View
  */
  public function index(): View
  {
    $coupon = $this->couponRepository->getAllCoupons();
    return view('content.e-commerce.backoffice.marketing.coupons.index', compact('coupon'));
  }

  /**
   * Muestra los detalles de un cupón específico.
   *
   * @param int $id
   * @return JsonResponse
  */
  public function show(int $id): JsonResponse
  {
    $coupon = $this->couponRepository->getCouponById($id);
    return response()->json($coupon);
  }

  /**
   * Almacena un nuevo cupón en la base de datos.
   *
   * @param StoreCouponRequest $request
   * @return JsonResponse
  */
  public function store(StoreCouponRequest $request): JsonResponse
  {
    $result = $this->couponRepository->createCoupon($request->validated());
    return response()->json($result);
  }

  /**
   * Muestra el formulario para editar un cupón existente.
   *
   * @param int $id
   * @return View
  */
  public function edit(int $id): View
  {
    $coupon = $this->couponRepository->getCouponById($id);
    return view('content.e-commerce.backoffice.marketing.coupons.edit', compact('coupon'));
  }

  /**
   * Actualiza un cupón existente en la base de datos.
   *
   * @param UpdateCouponRequest $request
   * @param int $id
   * @return JsonResponse
  */
  public function update(UpdateCouponRequest $request, int $id): JsonResponse
  {
    $result = $this->couponRepository->updateCoupon($id, $request->validated());
    return response()->json($result);
  }

  /**
   * Elimina un cupón específico de la base de datos.
   *
   * @param int $id
   * @return JsonResponse
  */
  public function destroy(int $id): JsonResponse
  {
    $result = $this->couponRepository->deleteCoupon($id);
    return response()->json($result);
  }

  /**
   * Elimina los cupones seleccionados de la base de datos.
   *
   * @param Request $request
   * @return JsonResponse
  */
  public function deleteSelected(Request $request): JsonResponse
  {
    $result = $this->couponRepository->deleteSelectedCoupons($request->ids);
    return response()->json($result);
  }

  /**
   * Obtiene los datos para mostrar en la tabla de cupones.
   *
   * @return mixed
  */
  public function datatable(): mixed
  {
    return $this->couponRepository->datatable();
  }

  public function getCouponByName($name): JsonResponse
  {
    $coupon = $this->couponRepository->getCouponByName($name);
    return response()->json([
       'coupon' => $coupon
    ]);
  }
}
