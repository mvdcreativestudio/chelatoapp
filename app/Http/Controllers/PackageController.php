<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Repositories\PackageRepository;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    protected $packageRepository;

    public function __construct(PackageRepository $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    public function index()
    {
        $packages = $this->packageRepository->getAll();
        return view('package.index', compact('packages'));
    }

    public function getAll()
    {
        $packages = $this->packageRepository->getAll();
        return response()->json([$packages]);
    }

    public function create()
    {
        return view('package.create');
    }

    public function store(StorePackageRequest $request)
    {
        $package = $this->packageRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Package creado con éxito.',
                'package' => $package
            ]);
        }

        return redirect()->route('package.index')->with('success', 'Package creado con éxito.');
    }

    public function show($id)
    {
        $package = $this->packageRepository->find($id);
        return view('package.show', compact('package'));
    }

    public function edit($id)
    {
        $package = $this->packageRepository->find($id);
        return view('package.edit', compact('package'));
    }

    public function update(UpdatePackageRequest $request, $id)
    {
        $this->packageRepository->update($id, $request->validated());
        return redirect()->route('package.index')->with('success', 'Package actualizado con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->packageRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Package borrado exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el package que se deseó borrar.'], 404);
        }    
    }

    public function updatePackageStock(Request $request, $id)
    {
        $request->validate([
            'stockToAdd' => 'required|integer|min:1',
        ]);

        $component = $this->packageRepository->updatePackageStock($id, $request->stockToAdd);

        if ($component) {
            return response()->json(['success' => true, 'message' => 'Stock updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error updating stock'], 400);
        }
    }
}
