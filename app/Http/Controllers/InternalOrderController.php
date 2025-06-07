<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternalOrderRequest;
use App\Repositories\InternalOrderRepository;
use App\Models\InternalOrder;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;



class InternalOrderController extends Controller
{
    protected $repository;

    public function __construct(InternalOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $orders = $this->repository->all();
        return view('internal_orders.index', compact('orders'));
    }

    public function create()
    {
        $data = $this->repository->getFormData();
        return view('internal-orders.create', $data);
    }

    public function getStoreProducts(Store $store)
    {
        $products = Product::with('categories')
            ->where('store_id', $store->id)
            ->where('allow_internal_order', true)
            ->get();

        $grouped = $products->groupBy(function ($product) {
            return $product->categories->first()->name ?? 'Sin categoría';
        });

        return view('internal-orders.partials.products-by-category', compact('grouped'));
    }


    public function store(StoreInternalOrderRequest $request)
    {
        $data = $request->validated();

        $order = $this->repository->create($data);

        return redirect()->route('internal-orders.create')->with('success', 'Orden creada correctamente.');
    }

    public function received()
    {
        $data = $this->repository->getReceivedOrders();

        return view('internal-orders.received', $data);
    }

    public function show(InternalOrder $order)
    {
        $order->load('fromStore', 'items.product');
        return view('internal-orders.show', compact('order'));
    }

    public function update(Request $request, InternalOrder $order)
    {
        $this->repository->updateReceivedOrder($order, $request->all());

        return redirect()->route('internal-orders.show', $order->id)
                        ->with('success', 'Orden actualizada correctamente.');
    }


    public function generatePdf(InternalOrder $order)
    {
        $order->load(['fromStore', 'toStore', 'items.product']);

        $pdf = Pdf::loadView('internal-orders.pdf', compact('order'));

        return $pdf->download("orden-interna-{$order->id}.pdf");
    }


}
