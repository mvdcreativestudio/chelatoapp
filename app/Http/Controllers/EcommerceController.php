<?php

namespace App\Http\Controllers;

use App\Repositories\EcommerceRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EcommerceController extends Controller
{
  /**
   * El repositorio de Ecommerce.
   *
   * @var EcommerceRepository
  */
  protected $ecommerceRepository;

  /**
   * Inyecta el repositorio en el controlador.
   *
   * @param EcommerceRepository $ecommerceRepository
  */
  public function __construct(EcommerceRepository $ecommerceRepository)
  {
    $this->middleware('ensure_store_selected')->only('store');
    $this->middleware('ensure_store_matches')->only('store');

    $this->ecommerceRepository = $ecommerceRepository;
  }

  /**
   * Muestra la página de selección de empresa.
   *
   * @return View
  */
  public function index(): View
  {
    $stores = $this->ecommerceRepository->getAllStores();
    return view('content.e-commerce.front.index', compact('stores'));
  }

  /**
   * Muestra la página de una empresa específica.
   *
   * @param string $slug
   * @return RedirectResponse|View
  */
  public function store(string $slug): RedirectResponse|View
  {
    $result = $this->ecommerceRepository->getStoreData($slug);

    if ($result['status'] === 'error') {
        return redirect()->route('home')->with('error', $result['message']);
    }

    return view('content.e-commerce.front.store', [
        'categories' => $result['categories'],
        'flavors' => $result['flavors'],
        'store' => $result['store']
    ]);
  }

  /**
   * Muestra la página de marketing.
   *
   * @return View
  */
  public function marketing(): View
  {
      return view('content.e-commerce.backoffice.marketing');
  }

  /**
   * Muestra la página de ajustes.
   *
   * @return View
  */
  public function settings(): View
  {
      return view('content.e-commerce.backoffice.settings');
  }

  /**
   * Muestra la pagina principal ('/') en base a la empresa o si no tiene ninguna seleccionada
   *
   * @return RedirectResponse
  */
  public function home(): RedirectResponse
  {
    $returnUrl = $this->ecommerceRepository->home();
    return redirect($returnUrl);
  }

}
