<?php

namespace App\Repositories;

use App\Models\Production;
use App\Models\Product;
use App\Models\Flavor;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductionRepository
{
    /**
     * Devuelve todas las producciones.
     *
     * @return Collection
     */
    public function getAllProductions(): Collection
    {
      return Production::with(['product', 'flavor'])->get();
    }

    /**
     * Devuelve todas las producciones en formato DataTable.
     *
     * @return \Yajra\DataTables\DataTableAbstract
    */
    public function getProductionsForDataTable(): JsonResponse
    {
      $productions = Production::with(['product', 'flavor'])->select('productions.*');

      return DataTables::of($productions)
        ->addColumn('product_name', function ($production) {
          return $production->product ? $production->product->name : 'N/A';
        })
        ->addColumn('flavor_name', function ($production) {
          return $production->flavor ? $production->flavor->name : 'N/A';
        })
        ->addColumn('actions', function ($production) {
          return '<a href="'.route('productions.edit', $production->id).'" class="btn btn-sm btn-primary">Editar</a>
                  <button class="btn btn-sm btn-danger delete-button" data-id="'.$production->id.'">Eliminar</button>';
        })
        ->rawColumns(['actions'])
        ->make(true);
    }

    /**
     * Muestra el formulario para crear una nueva producción.
     *
     * @return array
    */
    public function create(): array
    {
        // Obtener solo los productos que tienen al menos una receta
        $products = Product::with(['recipes.rawMaterial', 'recipes.usedFlavor'])
            ->where('is_trash', false)
            ->whereHas('recipes', function($query) {
                $query->whereNotNull('raw_material_id')->orWhereNotNull('used_flavor_id');
            })
            ->get();

        // Obtener solo los variaciones que tienen al menos una receta
        $flavors = Flavor::with(['recipes.rawMaterial', 'recipes.usedFlavor'])
            ->whereHas('recipes', function($query) {
                $query->whereNotNull('raw_material_id')->orWhereNotNull('used_flavor_id');
            })
            ->get();

        return compact('products', 'flavors');
    }


    /**
      * Almacena una nueva producción en la base de datos.
      *
      * @param array  $data
      * @return Collection
    */
    public function store(array $data, $user): array
    {
        $productions = new Collection();
        $insufficientStock = [];

        // Validación de stock
        foreach ($data['elaborations'] as $elaboration) {
            $productOrFlavor = explode('_', $elaboration['product_or_flavor']);
            $type = $productOrFlavor[0];
            $id = $productOrFlavor[1];
            $quantity = $elaboration['quantity'];

            $item = $type === 'product' ? Product::find($id) : Flavor::find($id);

            if ($user->cannot('access_bypass_raw_material_check')) {
                $recipes = $item->recipes;
                foreach ($recipes as $recipe) {
                    $requiredQuantity = $recipe->quantity * $quantity;
                    if ($recipe->raw_material_id) {
                        $rawMaterial = $recipe->rawMaterial;
                        if ($rawMaterial->stock < $requiredQuantity) {
                            $insufficientStock[] = $rawMaterial->name;
                        }
                    } elseif ($recipe->used_flavor_id) {
                        $usedFlavor = $recipe->usedFlavor;
                        if ($usedFlavor->stock < $requiredQuantity) {
                            $insufficientStock[] = $usedFlavor->name;
                        }
                    }
                }
            }
        }

        if (!empty($insufficientStock)) {
            return [
                'status' => 'error',
                'message' => 'Stock insuficiente para: ' . implode(', ', $insufficientStock)
            ];
        }

        // Actualización de stock y creación de producción
        foreach ($data['elaborations'] as $elaboration) {
            $productOrFlavor = explode('_', $elaboration['product_or_flavor']);
            $type = $productOrFlavor[0];
            $id = $productOrFlavor[1];
            $quantity = $elaboration['quantity'];

            $item = $type === 'product' ? Product::find($id) : Flavor::find($id);

            if ($user->cannot('access_bypass_raw_material_check')) {
                $recipes = $item->recipes;
                foreach ($recipes as $recipe) {
                    $requiredQuantity = $recipe->quantity * $quantity;
                    if ($recipe->raw_material_id) {
                        $rawMaterial = $recipe->rawMaterial;
                        $rawMaterial->stock -= $requiredQuantity;
                        $rawMaterial->save();
                    } elseif ($recipe->used_flavor_id) {
                        $usedFlavor = $recipe->usedFlavor;
                        $usedFlavor->stock -= $requiredQuantity;
                        $usedFlavor->save();
                    }
                }
            }

            $item->stock += $quantity;
            $item->save();

            $productionData = [
                'quantity' => $quantity,
                'product_id' => $type === 'product' ? $id : null,
                'flavor_id' => $type === 'flavor' ? $id : null,
            ];

            $production = Production::create($productionData);
            $productions->push($production);
        }

        return [
            'status' => 'success',
            'productions' => $productions
        ];
    }

    /**
     * Desactiva una producción.
     *
     * @param Production $production
     * @return bool
    */
    public function deactivate(Production $production): bool
    {
        try {
            Log::info('Iniciando desactivación de producción', ['production_id' => $production->id]);

            if ($production->product) {
                $item = $production->product;
                Log::info('Producto encontrado', ['product_id' => $item->id, 'product_name' => $item->name]);
            } elseif ($production->flavor) {
                $item = $production->flavor;
                Log::info('Sabor encontrado', ['flavor_id' => $item->id, 'flavor_name' => $item->name]);
            } else {
                Log::error('No se encontró ni producto ni sabor para la producción', ['production_id' => $production->id]);
                return false;
            }

            if ($item->exists && $production->status === 'active') {
                $item->stock -= $production->quantity;
                $item->save();

                $production->status = 'inactive';
                return $production->save();
            }

            Log::error('No se pudo desactivar la producción, condiciones no cumplidas', ['item_exists' => $item->exists, 'production_status' => $production->status]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error al desactivar la producción', ['production_id' => $production->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Activa una producción.
     *
     * @param Production $production
     * @return bool
    */
    public function activate(Production $production): bool
    {
        try {
            Log::info('Iniciando activación de producción', ['production_id' => $production->id]);

            if ($production->product) {
                $item = $production->product;
                Log::info('Producto encontrado', ['product_id' => $item->id, 'product_name' => $item->name]);
            } elseif ($production->flavor) {
                $item = $production->flavor;
                Log::info('Sabor encontrado', ['flavor_id' => $item->id, 'flavor_name' => $item->name]);
            } else {
                Log::error('No se encontró ni producto ni sabor para la producción', ['production_id' => $production->id]);
                return false;
            }

            if ($item->exists && $production->status === 'inactive') {
                $item->stock += $production->quantity;
                $item->save();

                $production->status = 'active';
                return $production->save();
            }

            Log::error('No se pudo activar la producción, condiciones no cumplidas', ['item_exists' => $item->exists, 'production_status' => $production->status]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error al activar la producción', ['production_id' => $production->id, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
