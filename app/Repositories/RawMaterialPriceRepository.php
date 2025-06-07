<?php

namespace App\Repositories;

use App\Models\RawMaterialPrice;

class RawMaterialPriceRepository
{
    public function getAll()
    {
        return RawMaterialPrice::select('raw_material_prices.*', 'raw_materials.name as raw_material_name')
            ->join('raw_materials', 'raw_material_prices.raw_material_id', '=', 'raw_materials.id')
            ->get();
    }

    public function find($id)
    {
        return RawMaterialPrice::findOrFail($id);
    }

    public function create(array $data)
    {
        return RawMaterialPrice::create($data);
    }

    public function getById($id)
    {
        $element = RawMaterialPrice::where('raw_material_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($element) {
            return response()->json([
                'price' => $element->price,
                'currency' => $element->currency,
                'message' => 'Precio de materia prima encontrado exitosamente.'
            ]);
        } else {
            return response()->json([
                'price' => null,
                'message' => 'No se encontró un precio anterior para esta materia prima.'
            ]);
        }
    }



    public function update($id, array $data)
    {
        $rawMaterialPrice = $this->find($id);
        $rawMaterialPrice->update($data);
        return $rawMaterialPrice;
    }

    public function delete($id)
    {
        $rawMaterialPrice = RawMaterialPrice::find($id);
        return $rawMaterialPrice ? $rawMaterialPrice->delete() : false;
    }
}
