<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CartRepository;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Requests\SelectStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
  /**
   * El repositorio para la gestión del carrito de compras.
   *
   * @var CartRepository
  */
  protected $cartRepository;

  /**
   * Crea una nueva instancia del controlador.
   *
   * @param CartRepository $cartRepository
  */
  public function __construct(CartRepository $cartRepository)
  {
    $this->cartRepository = $cartRepository;
  }

  /**
   * Muestra la página del carrito de compras.
   *
   * @return View
  */
  public function index(): View
  {
    return view('content.e-commerce.front.cart');
  }

  /**
   * Seleccionar una empresa.
   *
   * @param SelectStoreRequest $request
   * @return RedirectResponse
  */
  public function selectStore(SelectStoreRequest $request): RedirectResponse
  {
    return $this->cartRepository->selectStore($request);
  }

  /**
   * Añadir un producto al carrito.
   *
   * @param AddToCartRequest $request
   * @param int $productId
   * @return RedirectResponse
  */
  public function addToCart(AddToCartRequest $request, int $productId): RedirectResponse
  {
    $result = $this->cartRepository->addProduct($request, $productId);
    return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
  }

  /**
   * Actualizar la cantidad de un producto en el carrito.
   *
   * @param UpdateCartRequest $request
   * @return RedirectResponse
  */
  public function updateCart(UpdateCartRequest $request): RedirectResponse
  {
    $result = $this->cartRepository->updateProductQuantity($request->id, $request->quantity);
    return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
  }

  /**
   * Eliminar un producto del carrito.
   *
   * @param Request $request
   * @return RedirectResponse
  */
  public function removeItem(Request $request): RedirectResponse
  {
    $result = $this->cartRepository->removeItem($request->key);
    return redirect()->back()->with($result['status'], $result['message']);
  }

  /**
   * Limpiar el carrito.
   *
   * @return RedirectResponse
  */
  public function clearCart(): RedirectResponse
  {
    $this->cartRepository->clearCart();
    return redirect()->back()->with('success', 'Carrito vaciado con éxito.');
  }

  /**
   * Limpiar toda la sesión.
   *
   * @return RedirectResponse
  */
  public function clearSession(): RedirectResponse
  {
    $this->cartRepository->clearSession();
    return redirect()->to('/')->with('success', 'Sesión limpiada correctamente.');
  }
}
