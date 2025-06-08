<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SupplierImport;
use App\Exports\SupplierTemplateImport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exports\SuppliersExport;

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
     * Muestra una lista de todos los proveedores y ordenes.
     *
     * @return View
     */
    public function index(): View
    {
        $suppliers = $this->supplierRepository->getAllWithOrders();
        return view('suppliers.index', $suppliers);
    }


    /**
     * Devuelve a todos los proveedores.
     *
     */
    public function getAll()
    {
        $suppliers = $this->supplierRepository->getAll();
        return response()->json($suppliers);
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
        try {
            $data = $request->validated();
            $data['store_id'] = auth()->user()->store_id;
            Log::info('Creando proveedor con datos: ', $data);

            $this->supplierRepository->create($data);
            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Proveedor creado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el proveedor: ' . $e->getMessage());
        }
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

    /**
     * Descarga una plantilla para importar proveedores.
     *
     * @param Request $request
     * @return mixed
     */
    public function downloadTemplate(Request $request)
    {
        try {
            return Excel::download(new SupplierTemplateImport, 'plantilla_proveedores.xlsx');
        } catch (\Exception $e) {
            Log::error('Error al descargar la plantilla: ' . $e->getMessage());
            return back()->with('error', 'Error al descargar la plantilla');
        }
    }


    /**
     * Importa proveedores desde un archivo Excel.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            $store_id = Auth::user()->store_id;

            DB::beginTransaction();

            Excel::import(new SupplierImport($store_id), $request->file('file'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proveedores importados correctamente'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = "Fila {$failure->row()}: {$failure->errors()[0]}";
            }

            Log::error('Error en la validación de importación: ', $errors);

            return response()->json([
                'success' => false,
                'message' => 'Error en la validación de datos',
                'errors' => $errors
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al importar proveedores: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al importar proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar los proveedores a un archivo Excel.
     *
     */
    public function export()
    {
        return Excel::download(new SuppliersExport, 'proveedores.xlsx');
    }
}
