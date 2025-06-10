<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\User;
use App\Repositories\VendorRepository;
use App\Http\Requests\StoreVendorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    protected $vendorRepository;

    public function __construct(VendorRepository $vendorRepository)
    {
        $this->vendorRepository = $vendorRepository;
    }

    public function index()
    {
        $vendors = $this->vendorRepository->getAll();
        $users = User::select('id', 'name', 'email')->get();
        return view('vendors.index', compact('vendors', 'users'));
    }

    public function store(StoreVendorRequest $request)
    {
        try {
            $this->vendorRepository->create($request->validated());
            return redirect()->route('vendors.index')
                ->with('success', 'Vendedor creado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el vendedor: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $vendor = $this->vendorRepository->find($id);
        return response()->json($vendor);
    }

    public function update(Request $request, $id)
    {
        try {
            $vendor = $this->vendorRepository->update($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Vendedor actualizado exitosamente',
                'data' => $vendor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el vendedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->vendorRepository->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'Vendedor eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el vendedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
