<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierController extends Controller
{
    /**
     * El repositorio para las operaciones de proveedores.
     *
     * @var SupplierRepository
     */
    protected $supplierRepository;

    /**
     * Inyecta el repositorio en el controlador.
     *
     * @param SupplierRepository $supplierRepository
     */
    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->middleware(['check_permission:access_suppliers', 'user_has_store'])->only(
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

        $this->supplierRepository = $supplierRepository;
    }

    /**
     * Muestra una lista de todos los proveedores.
     *
     * @return View
     */
    public function index(): View
    {
        $suppliers = $this->supplierRepository->getAll();
        return view('suppliers.index', $suppliers);
    }

    /**
     * Muestra el formulario para crear un nuevo proveedor.
     *
     * @return View
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Almacena un nuevo proveedor en la base de datos.
     *
     * @param StoreSupplierRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->supplierRepository->create($request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado correctamente.');
    }

    /**
     * Muestra un proveedor específico.
     *
     * @param Supplier $supplier
     * @return View
     */
    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Muestra el formulario para editar un proveedor existente.
     *
     * @param Supplier $supplier
     * @return View
     */
    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Actualiza un proveedor específico en la base de datos.
     *
     * @param UpdateSupplierRequest $request
     * @param Supplier $supplier
     * @return RedirectResponse
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->supplierRepository->update($supplier, $request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Elimina un proveedor de la base de datos.
     *
     * @param Supplier $supplier
     * @return RedirectResponse
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->supplierRepository->delete($supplier);
        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado correctamente.');
    }
}
