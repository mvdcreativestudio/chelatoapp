<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Repositories\ProductionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductionController extends Controller
{
    /**
     * El repositorio para las producciones.
     *
     * @var ProductionRepository
    */
    protected $productionRepository;

    /**
     * Inyecta el repositorio en el controlador.
     *
     * @param ProductionRepository $productionRepository
    */
    public function __construct(ProductionRepository $productionRepository)
    {
      $this->middleware(['check_permission:access_productions', 'user_has_store'])->only(
          [
              'index',
              'create',
              'store',
              'show',
              'destroy'
          ]
      );

      $this->productionRepository = $productionRepository;
    }

    /**
     * Muestra una lista de todas las producciones.
     *
     * @return View
    */
    public function index(): View
    {
      $productions = $this->productionRepository->getAllProductions();

      return view('productions.index', compact('productions'));
    }

    /**
     * Devuelve todas las producciones en formato DataTable.
     *
     * @return mixed
    */
    public function datatable(): mixed
    {
      return $this->productionRepository->getProductionsForDataTable();
    }

    /**
     * Muestra el formulario para crear una nueva producción.
     *
     * @return View
    */
    public function create(): View
    {
      $productionData = $this->productionRepository->create();
      return view('productions.create', $productionData);
    }

    /**
     * Almacena una nueva producción en la base de datos.
     *
     * @param  Request  $request
     * @return RedirectResponse
    */
    public function store(Request $request): RedirectResponse
    {
      $request->validate([
        'elaborations.*.product_or_flavor' => 'required|string',
        'elaborations.*.quantity' => 'required|integer|min:1',
      ]);

      $user = Auth::user();

      $result = $this->productionRepository->store($request->all(), $user);

      if ($result['status'] === 'error') {
          return redirect()->back()->withErrors($result['message']);
      }

      return redirect()->route('productions.index')->with('success', 'Producción creada correctamente.');
    }

    /**
     * Muestra una producción específica.
     *
     * @param  Production  $production
     * @return View
    */
    public function show(Production $production): View
    {
      return view('productions.show', compact('production'));
    }

    /**
     * Desactiva una produccion.
     *
     * @param  Production  $production
     * @return RedirectResponse
    */

    public function destroy(Production $production): RedirectResponse
    {
        try {
            $result = $this->productionRepository->deactivate($production);
            if ($result) {
                return redirect()->route('productions.index')->with('success', 'Producción desactivada correctamente.');
            } else {
                return redirect()->route('productions.index')->with('error', 'No se pudo desactivar la producción.');
            }
        } catch (\Exception $e) {
            Log::info('Error al desactivar la producción: ' . $e->getMessage());
            return redirect()->route('productions.index')->with('error', 'Ocurrió un error al intentar desactivar la producción.');
        }
    }


    /**
     * Activa una producción.
     *
     * @param Production $production
     * @return RedirectResponse
    */
    public function activate(Production $production): RedirectResponse
    {
        $result = $this->productionRepository->activate($production);
        if ($result) {
            return redirect()->route('productions.index')->with('success', 'Producción activada correctamente.');
        } else {
            return redirect()->route('productions.index')->with('error', 'No se pudo activar la producción.');
        }
    }
}
