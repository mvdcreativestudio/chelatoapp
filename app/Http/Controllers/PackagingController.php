<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackagingRequest;
use App\Http\Requests\UpdatePackagingRequest;
use App\Repositories\PackagingRepository;
use Illuminate\Http\Request;

class PackagingController extends Controller
{
    protected $packagingRepository;

    public function __construct(PackagingRepository $packagingRepository)
    {
        $this->packagingRepository = $packagingRepository;
    }

    public function index()
    {
        $packagings = $this->packagingRepository->getAll();
        return view('packaging.index', compact('packagings'));
    }

    public function create()
    {
        return view('packaging.create');
    }

    public function store(StorePackagingRequest $request)
    {
        // Usar el repositorio para manejar la lógica de empaquetado
        $packaging = $this->packagingRepository->createAndHandlePackaging($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Empaque creado con éxito.',
                'packaging' => $packaging
            ]);
        }

        return redirect()->route('packagings.index')->with('success', 'Empaque creado con éxito.');
    }


    public function show($id)
    {
        $packaging = $this->packagingRepository->find($id);
        return view('packaging.show', compact('packaging'));
    }

    public function edit($id)
    {
        $packaging = $this->packagingRepository->find($id);
        return view('packaging.edit', compact('packaging'));
    }

    public function update(UpdatePackagingRequest $request, $id)
    {
        $this->packagingRepository->update($id, $request->validated());
        return redirect()->route('packaging.index')->with('success', 'Empaque actualizado con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->packagingRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Empaque eliminado exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el empaque que se deseó eliminar.'], 404);
        }
    }

    public function startProduction()
    {
        $bulkProductions = $this->packagingRepository->startProduction();

        return response()->json($bulkProductions);
    }
}
