<?php

namespace App\Http\Controllers;

use App\Models\LeadCategories;
use App\Models\Lead;
use App\Http\Requests\StoreLeadCategoryRequest;
use App\Http\Requests\UpdateLeadCategoryRequest;
use App\Repositories\LeadCategoriesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LeadCategoriesController extends Controller
{
    protected $leadCategoriesRepository;

    public function __construct(LeadCategoriesRepository $leadCategoriesRepository)
    {
        $this->leadCategoriesRepository = $leadCategoriesRepository;
        $this->middleware('auth'); // Asegúrate de que el middleware de autenticación esté aplicado
    }

    public function index()
    {
        $categories = $this->leadCategoriesRepository->getAll();
        return response()->json(['categories' => $categories]);
    }

    public function show(LeadCategories $leadCategory)
    {
        return response()->json(['category' => $leadCategory]);
    }

    public function store(StoreLeadCategoryRequest $request)
    {
        try {
            // Log para debug
            /*
            \Log::debug('StoreLeadCategory hit', [
                'user' => Auth::user() ? Auth::user()->id : 'no user',
                'data' => $request->validated(),
                'headers' => $request->headers->all()
            ]);*/
            
            $category = $this->leadCategoriesRepository->create($request->validated());
            return response()->json(['success' => true, 'category' => $category], 201);
        } catch (\Exception $e) {
            Log::error('Error creating lead category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateLeadCategoryRequest $request, LeadCategories $leadCategory)
    {
        try {
            $category = $this->leadCategoriesRepository->update($leadCategory->id, $request->validated());
            return response()->json(['success' => true, 'category' => $category]);
        } catch (\Exception $e) {
            Log::error('Error updating lead category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, LeadCategories $leadCategory)
    {
        try {
            // Si hay leads en la categoría, requerimos una categoría destino
            if (Lead::where('category_id', $leadCategory->id)->exists()) {
                $request->validate([
                    'target_category_id' => 'required|exists:lead_categories,id'
                ]);

                // Mover todos los leads a la categoría destino
                Lead::where('category_id', $leadCategory->id)
                    ->update(['category_id' => $request->target_category_id]);
            }

            // Eliminar la categoría
            $this->leadCategoriesRepository->delete($leadCategory->id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting lead category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
