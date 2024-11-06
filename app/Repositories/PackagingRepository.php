<?php

namespace App\Repositories;

use App\Models\Packaging;
use App\Models\Package;
use App\Models\Formula;
use App\Models\BulkProduction;
use App\Models\PackageComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackagingRepository
{
    public function getAll()
    {
        return DB::table('packagings')
            ->join('packages', 'packagings.package_id', '=', 'packages.id')
            ->join('bulk_productions', 'packagings.bulk_production_id', '=', 'bulk_productions.id')
            ->join('formulas', 'bulk_productions.formula_id', '=', 'formulas.id')
            ->select(
                'packagings.*',
                'packages.name as package_name',
                'bulk_productions.formula_id as formula_id',
                'formulas.name as formula_name'
            )
            ->get();
    }


    public function find($id)
    {
        return Packaging::findOrFail($id);
    }

    public function create(array $data)
    {
        return Packaging::create($data);
    }

    public function update($id, array $data)
    {
        $packaging = $this->find($id);
        $packaging->update($data);
        return $packaging;
    }

    public function delete($id)
    {
        $packaging = Packaging::find($id);
        return $packaging->delete();
    }

    public function startProduction()
    {
        // Traemos las bulk productions junto con la información de la fórmula
        return DB::table('bulk_productions')
            ->join('formulas', 'bulk_productions.formula_id', '=', 'formulas.id')
            ->select(
                'bulk_productions.id as bulk_production_id',
                'bulk_productions.quantity_produced',
                'bulk_productions.quantity_used',
                DB::raw('bulk_productions.quantity_produced - bulk_productions.quantity_used as quantity_available'), // Cantidad disponible
                'bulk_productions.production_date',
                'formulas.name as formula_name',
                'formulas.description as formula_description',
                'formulas.unit_of_measure as formula_unit_of_measure',
                'formulas.quantity as formula_quantity',
                'formulas.id as formula_id',
                'bulk_productions.batch_number'
            )
            ->having('quantity_available', '>', 0) // Mostrar solo las producciones que aún tienen cantidad disponible
            ->get();
    }

    public function createAndHandlePackaging(array $data)
    {
        Log::info($data);
        DB::beginTransaction();

        try {
            $packaging = Packaging::create($data);

            $bulkProduction = BulkProduction::with('formula.product')->find($data['bulk_production_id']);

            if (!$bulkProduction) {
                throw new \Exception('Bulk production not found');
            }

            $bulkProduction->quantity_used += $data['quantity_used'];

            if (!$bulkProduction->save()) {
                throw new \Exception('Failed to update bulk production.');
            }

            $package = Package::find($data['package_id']);
            $package->stock -= $data['quantity_packaged'];

            if (!$package->save()) {
                throw new \Exception('Failed to update package stock.');
            }

            $finalProduct = $bulkProduction->formula->product;
            if ($finalProduct) {
                $finalProduct->stock += $data['quantity_packaged'];
                if (!$finalProduct->save()) {
                    throw new \Exception('Failed to update final product stock.');
                }
            }

            // Verificar y actualizar el stock de la etiqueta
            if (!empty($data['label_id'])) {
                $labelComponent = PackageComponent::find($data['label_id']);
                if ($labelComponent) {
                    $labelComponent->stock -= $data['quantity_packaged'];
                    if (!$labelComponent->save()) {
                        throw new \Exception('Failed to update label component stock.');
                    }
                } else {
                    throw new \Exception('Label component not found.');
                }
            }

            // Verificar y actualizar el stock de la tapa 
            if (!empty($data['tap_id'])) {
                $tapComponent = PackageComponent::find($data['tap_id']);
                if ($tapComponent) {
                    $tapComponent->stock -= $data['quantity_packaged'];
                    if (!$tapComponent->save()) {
                        throw new \Exception('Failed to update tap component stock.');
                    }
                } else {
                    throw new \Exception('Tap component not found.');
                }
            }

            DB::commit();
            return $packaging;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in createAndHandlePackaging: ' . $e->getMessage());
            throw $e;
        }
    }


    // Helper function to convert to milliliters
    private function convertToMl($value, $unit)
    {
        if ($unit === 'l') {
            return $value * 1000; // Convert liters to milliliters
        }
        return $value; // Already in milliliters
    }
}
