<?php

namespace App\Http\Controllers;

use App\Repositories\LandingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingController extends Controller
{

  /**
   * El repositorio Landing.
   *
   * @var LandingRepository
  */
  protected $landingRepository;

  /**
   * Inyecta el repositorio en el controlador.
   *
   * @param LandingRepository $landingRepository
  */
  public function __construct(LandingRepository $landingRepository)
  {
    $this->landingRepository = $landingRepository;
  }

  /**
  * Muestra la página de inicio.
  * 
  * @return View
  */
  public function index(): View {
      return view('content.landing.index');
  }

  /**
  * Muestra la categoría Colchones
  * 
  * @return View
  */
  public function colchones(): View
  {
      $products = $this->landingRepository->getProductsWithCategories();
       return view('content.landing.colchones', compact('products'));
  }
   
  /**
  * Vista del producto individual
  * 
  * @param int $id
  * @return View
  */
  public function showProduct($id): View
  {
      $product = $this->landingRepository->getProductById($id);
      return view('content.landing.product-detail', compact('product'));
  }


}