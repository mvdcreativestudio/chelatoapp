<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageComponentRequest;
use App\Http\Requests\UpdatePackageComponentRequest;
use App\Repositories\PackageComponentRepository;
use Illuminate\Http\Request;

class PackageComponentController extends Controller
{
    protected $packageComponentRepository;

    public function __construct(PackageComponentRepository $packageComponentRepository)
    {
        $this->packageComponentRepository = $packageComponentRepository;
    }

    public function index()
    {
        $packageComponents = $this->packageComponentRepository->getAll();
        return view('package-components.index', compact('packageComponents'));
    }

    public function getAll()
    {
        $packageComponents = $this->packageComponentRepository->getAll();
        return response()->json(['packageComponents' => $packageComponents]);
    }

    public function create()
    {
        return view('package-components.create');
    }

    public function store(StorePackageComponentRequest $request)
    {
        $packageComponent = $this->packageComponentRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Package Component created successfully.',
                'packageComponent' => $packageComponent
            ]);
        }

        return redirect()->route('package-components.index')->with('success', 'Package Component created successfully.');
    }

    public function show($id)
    {
        $packageComponent = $this->packageComponentRepository->find($id);
        return view('package-components.show', compact('packageComponent'));
    }

    public function edit($id)
    {
        $packageComponent = $this->packageComponentRepository->find($id);
        return view('package-components.edit', compact('packageComponent'));
    }

    public function update(UpdatePackageComponentRequest $request, $id)
    {
        $this->packageComponentRepository->update($id, $request->validated());
        return redirect()->route('package-components.index')->with('success', 'Package Component updated successfully.');
    }

    public function destroy($id)
    {
        $deleted = $this->packageComponentRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Package Component deleted successfully.']);
        } else {
            return response()->json(['message' => 'Could not find the Package Component to delete.'], 404);
        }
    }

    public function getComponents()
    {
        $components = $this->packageComponentRepository->getAll();

        $taps = $components->where('type', 'cap');
        $labels = $components->where('type', 'label');

        return response()->json([
            'taps' => $taps,
            'labels' => $labels,
        ]);
    }

    public function updatePackageComponentStock(Request $request, $id)
    {
        $request->validate([
            'stockToAdd' => 'required|integer|min:1',
        ]);

        $component = $this->packageComponentRepository->updatePackageComponentStock($id, $request->stockToAdd);

        if ($component) {
            return response()->json(['success' => true, 'message' => 'Stock updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error updating stock'], 400);
        }
    }
}
