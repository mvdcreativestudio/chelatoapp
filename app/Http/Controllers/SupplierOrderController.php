<?php

namespace App\Http\Controllers;

use App\Repositories\SupplierOrderRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\RawMaterialRepository;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreSupplierOrderRequest;
use App\Http\Requests\UpdateSupplierOrderRequest;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierOrderController extends Controller
{
    /**
     * El repositorio para las operaciones de ordenes de compra.
     *
     * @var SupplierOrderRepository
     * @var SupplierRepository
     * @var RawMaterialRepository
    */
    protected $supplierOrderRepository;
    protected $supplierRepository;
    protected $rawMaterialRepository;


    /**
     * Inyecta el repositorio en el controlador.
     *
     * @param SupplierOrderRepository $supplierOrderRepository
    */
    public function __construct(SupplierOrderRepository $supplierOrderRepository, SupplierRepository $supplierRepository, RawMaterialRepository $rawMaterialRepository)
    {
        $this->middleware(['check_permission:access_supplier-orders', 'user_has_store'])->only(
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

        $this->supplierOrderRepository = $supplierOrderRepository;
        $this->supplierRepository = $supplierRepository;
        $this->rawMaterialRepository = $rawMaterialRepository;
    }

    /**
     * Muestra una lista de todas las ordenes de compra.
     *
     * @return View
    */
    public function index(): View
    {
        $supplierOrders = $this->supplierOrderRepository->getAll();
        return view('supplier-orders.index', compact('supplierOrders'));
    }

    /**
     * Muestra el formulario para crear una nueva orden de compra.
     *
     * @return View
    */
    public function create(): View
    {
        $store_id = auth()->user()->store_id;

        $suppliers = $this->supplierRepository->findByStoreId($store_id);
        $rawMaterials = $this->rawMaterialRepository->findByStoreId($store_id);

        return view('supplier-orders.create', compact('suppliers', 'rawMaterials'));
    }

    /**
     * Almacena una nueva orden de compra en la base de datos.
     *
     * @param  StoreSupplierOrderRequest $request
     * @return RedirectResponse
    */
    public function store(StoreSupplierOrderRequest $request): RedirectResponse
    {
        $this->supplierOrderRepository->create($request->all());
        return redirect()->route('supplier-orders.index')->with('success', 'Orden creada correctamente.');
    }

    /**
     * Muestra una orden de compra específica.
     *
     * @param  int $id
     * @return View
    */
    public function show($id): View
    {
        $supplierOrder = $this->supplierOrderRepository->findById($id);
        return view('supplier-orders.show', compact('supplierOrder'));
    }

    /**
     * Muestra el formulario para editar una orden de compra existente.
     *
     * @param  int $id
     * @return View
    */
    public function edit($id): View
    {
        $supplierOrder = $this->supplierOrderRepository->findById($id);

        $store_id = auth()->user()->store_id;

        $suppliers = $this->supplierRepository->findByStoreId($store_id);
        $rawMaterials = $this->rawMaterialRepository->findByStoreId($store_id);

        return view('supplier-orders.edit', compact('supplierOrder', 'suppliers', 'rawMaterials'));
    }

      /**
       * Actualiza una orden de compra específica en la base de datos.
       *
       * @param  UpdateSupplierOrderRequest $request
       * @param  int $id
       * @return RedirectResponse
      */
      public function update(UpdateSupplierOrderRequest $request, $id): RedirectResponse
      {
          $this->supplierOrderRepository->update($id, $request->all());
          return redirect()->route('supplier-orders.index')->with('success', 'Orden actualizada correctamente.');
      }


    /**
     * Elimina una orden de compra específica de la base de datos.
     *
     * @param  int $id
     * @return RedirectResponse
    */
    public function destroy($id): RedirectResponse
    {
        $this->supplierOrderRepository->delete($id);
        return redirect()->route('supplier-orders.index')->with('success', 'Orden eliminada correctamente.');
    }

    /**
     * Genera un PDF para una orden de compra específica.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(int $id)
    {
        $order = $this->supplierOrderRepository->findOrderDetailsForPdf($id);

        $pdf = Pdf::loadView('supplier-orders.pdf', compact('order'));
        return $pdf->stream('supplier_order_'.$id.'.pdf');
    }
}
