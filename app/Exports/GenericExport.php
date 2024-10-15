<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericExport implements FromCollection, WithHeadings
{
    protected $data;

    // Constructor que recibe los datos
    public function __construct($data)
    {
        $this->data = $data;
    }

    // Método para devolver los datos
    public function collection()
    {
        // Define las columnas booleanas que quieres transformar
        $booleanColumns = ['status', 'draft']; // Añade aquí todas las columnas booleanas

        // Modificar las columnas booleanas antes de exportar
        $modifiedData = collect($this->data)->map(function ($item) use ($booleanColumns) {
            foreach ($booleanColumns as $column) {
                if (isset($item[$column])) {
                    $item[$column] = $item[$column] == 1 ? 'Si' : 'No';  // Cambia 1/0 por Sí/No
                }
            }
            return $item;
        });

        return $modifiedData;
    }

    // Método para generar dinámicamente los encabezados
    public function headings(): array
    {
        if (count($this->data) > 0) {
            $keys = array_keys($this->data[0]);  // Tomar las claves de la primera fila de datos

            // Mapea las claves a nombres más legibles (opcional)
            $readableHeadings = [
                'id' => 'ID',
                'name' => 'Nombre',
                'sku' => 'SKU',
                'description' => 'Descripción',
                'discount' => 'Descuento',
                'image' => 'Imagen',
                'store_id' => 'Tienda / Empresa',
                'status' => 'Estado',
                'stock' => 'Stock',
                'safety_margin' => 'Margen de seguridad',
                'draft' => 'Borrador',
                'is_trash' => 'Papelera',
                'created_at' => 'Fecha de creación',
                'updated_at' => 'Fecha de actualización',
                'type' => 'Tipo',
                'old_price' => 'Precio Anterior',
                'price' => 'Precio',
                // Agrega el resto de los mapeos de las claves a nombres legibles
            ];

            // Devuelve los encabezados legibles, si no existe un mapeo, usa la clave original
            return array_map(function ($key) use ($readableHeadings) {
                return $readableHeadings[$key] ?? $key;
            }, $keys);
        }

        return [];
    }
}
