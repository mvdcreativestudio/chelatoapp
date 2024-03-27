<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Repositories\RawMaterialRepository;
use App\Http\Requests\StoreRawMaterialRequest;
use App\Http\Requests\UpdateRawMaterialRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RawMaterialController extends Controller
{
    /**
     * El repositorio para las operaciones de materias primas.
     *
     * @var RawMaterialRepository
     */
    protected $rawMaterialRepository;

    /**
     * Inyecta el repositorio en el controlador.
     *
     * @param  RawMaterialRepository  $rawMaterialRepository
     */
    public function __construct(RawMaterialRepository $rawMaterialRepository)
    {
        $this->middleware(['check_permission:access_raw-materials', 'user_has_store'])->only(
            [
                'index',
                'create',
                'store',
                'show',
                'edit',
                'update',
                'destroy'
            ]
        );

        $this->rawMaterialRepository = $rawMaterialRepository;
    }

    /**
     * Muestra una lista de todas las materias primas.
     *
     * @return View
     */
    public function index(): View
    {
        if (!auth()->user()->store_id) {
            session()->flash('error', 'No se puede acceder a las materias primas sin una tienda asignada.');
            return view('raw-materials.index');
        }

        $rawMaterials = $this->rawMaterialRepository->getAll();

        $quantityByUnitOfMeasure = $rawMaterials
                                    ->groupBy('unit_of_measure')
                                    ->map(function ($item) {
                                        return $item->count();
                                    });


        return view('raw-materials.index', compact('rawMaterials', 'quantityByUnitOfMeasure'));
    }

    /**
     * Muestra el formulario para crear una nueva materia prima.
     *
     * @return View
     */
    public function create(): View
    {
        return view('raw-materials.create');
    }

    /**
     * Almacena una nueva materia prima en la base de datos.
     *
     * Aquí es donde se utiliza StoreRawMaterialRequest para validar la solicitud antes de proceder.
     *
     * @param  StoreRawMaterialRequest  $request
     * @return RedirectResponse
     */
    public function store(StoreRawMaterialRequest $request): RedirectResponse
    {
        try {
            $this->rawMaterialRepository->create($request->validated());
            return redirect()->route('raw-materials.index')->with('success', 'Materia prima creada correctamente.');
        } catch (ModelNotFoundException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra una materia prima específica.
     *
     * @param  RawMaterial  $rawMaterial
     * @return View
     */
    public function show(RawMaterial $rawMaterial): View
    {
        return view('raw-materials.show', compact('rawMaterial'));
    }

    /**
     * Muestra el formulario para editar una materia prima existente.
     *
     * @param  RawMaterial  $rawMaterial
     * @return View
     */
    public function edit(RawMaterial $rawMaterial): View
    {
        return view('raw-materials.edit', compact('rawMaterial'));
    }

    /**
     * Actualiza una materia prima específica en la base de datos.
     *
     * Aquí es donde se utiliza UpdateRawMaterialRequest para validar la solicitud antes de proceder.
     *
     * @param  UpdateRawMaterialRequest  $request
     * @param  RawMaterial  $rawMaterial
     * @return RedirectResponse
     */
    public function update(UpdateRawMaterialRequest $request, RawMaterial $rawMaterial): RedirectResponse
    {
        $this->rawMaterialRepository->update($rawMaterial, $request->validated());
        return redirect()->route('raw-materials.index')->with('success', 'Materia prima actualizada correctamente.');
    }

    /**
     * Elimina una materia prima de la base de datos.
     *
     * @param  RawMaterial  $rawMaterial
     * @return RedirectResponse
     */
    public function destroy(RawMaterial $rawMaterial): RedirectResponse
    {
        $this->rawMaterialRepository->delete($rawMaterial);
        return redirect()->route('raw-materials.index')->with('success', 'Materia prima eliminada correctamente.');
    }
}
