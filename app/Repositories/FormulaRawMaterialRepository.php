<?php

namespace App\Repositories;

use App\Models\FormulaRawMaterial;
use Illuminate\Support\Facades\Log;

class FormulaRawMaterialRepository
{
    public function getAll($id)
    {
        return FormulaRawMaterial::where('formula_id', $id)
            ->leftJoin('raw_materials', 'formula_raw_materials.raw_material_id', '=', 'raw_materials.id')
            ->select('formula_raw_materials.*', 'raw_materials.name')
            ->get();
    }



    public function find($id)
    {
        return FormulaRawMaterial::findOrFail($id);
    }

    public function create(array $data)
    {
        return FormulaRawMaterial::create($data);
    }

    public function update($id, array $data)
    {
        $formulaRawMaterial = $this->find($id);
        $formulaRawMaterial->update($data);
        return $formulaRawMaterial;
    }

    public function delete($id)
    {
        $formulaRawMaterial = FormulaRawMaterial::find($id);
        return $formulaRawMaterial->delete();
    }

    public function bulkInsert(array $rows, $formulaId)
    {
        foreach ($rows as $row) {
            try {
                FormulaRawMaterial::create([
                    'formula_id' => $formulaId,
                    'raw_material_id' => $row['raw_material_id'],
                    'quantity_required' => $row['quantity_required'],
                    'step' => $row['step'],
                    'clarification' => $row['clarification'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error("Error al insertar registro: " . $e->getMessage());
            }
        }
    }
}
