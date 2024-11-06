<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRawMaterialPriceRequest;
use App\Http\Requests\UpdateRawMaterialPriceRequest;
use App\Repositories\RawMaterialPriceRepository;
use Illuminate\Http\Request;

class RawMaterialPriceController extends Controller
{
    protected $rawMaterialPriceRepository;

    public function __construct(RawMaterialPriceRepository $rawMaterialPriceRepository)
    {
        $this->rawMaterialPriceRepository = $rawMaterialPriceRepository;
    }

    public function index()
    {
        $rawMaterialPrices = $this->rawMaterialPriceRepository->getAll();
        return view('raw-material-prices.index', compact('rawMaterialPrices'));
    }

    public function create()
    {
        return view('raw_material_prices.create');
    }

    public function store(StoreRawMaterialPriceRequest $request)
    {
        $rawMaterialPrice = $this->rawMaterialPriceRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Precio de materia prima creado con éxito.',
                'rawMaterialPrice' => $rawMaterialPrice
            ]);
        }

        return redirect()->route('raw-material-prices.index')->with('success', 'Precio de materia prima creado con éxito.');
    }

    public function show($id)
    {
        $rawMaterialPrice = $this->rawMaterialPriceRepository->find($id);
        return view('raw_material_prices.show', compact('rawMaterialPrice'));
    }

    public function edit($id)
    {
        $rawMaterialPrice = $this->rawMaterialPriceRepository->find($id);
        return view('raw_material_prices.edit', compact('rawMaterialPrice'));
    }

    public function update(UpdateRawMaterialPriceRequest $request, $id)
    {
        $this->rawMaterialPriceRepository->update($id, $request->validated());
        return redirect()->route('raw-material-prices.index')->with('success', 'Precio de materia prima actualizado con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->rawMaterialPriceRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Precio de materia prima eliminado exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el precio de materia prima que se deseaba eliminar.'], 404);
        }    
    }

    public function getById($id)
    {
        return $this->rawMaterialPriceRepository->getById($id);
    }

}
