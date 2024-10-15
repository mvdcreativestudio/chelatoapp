<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use App\Models\ProductCategory;

class ProductTemplateExport implements FromArray, WithHeadings, WithEvents
{
    protected $categories;
    protected $storeId;

    public function __construct($categories, $storeId)
    {
        $this->categories = $categories;
        $this->storeId = $storeId;
    }

    public function array(): array
    {
        // Devolver un array vacío porque no queremos productos por defecto
        return [];
    }

    public function headings(): array
    {
        return [
            'Nombre', 'SKU', 'Descripción', 'Precio', 'Precio_oferta', 'Descuento',
            'Imagen', 'Stock', 'Margen_seguridad', 'Categoria'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $storeCategories = $this->categories->where('store_id', $this->storeId)->pluck('name', 'id')->toArray();

                // Crear una hoja oculta para las categorías
                $workbook = $event->sheet->getDelegate()->getParent();
                $categoriesSheet = $workbook->createSheet();
                $categoriesSheet->setTitle('Categorias');

                // Agregar categorías a la hoja oculta
                $row = 1;
                foreach ($storeCategories as $id => $name) {
                    $categoriesSheet->setCellValue('A' . $row, $id . '- ' . $name);
                    $row++;
                }

                // Ocultar la hoja de categorías
                $categoriesSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);

                // Añadir el dropdown en la columna J (Categoría) para las primeras 1000 filas
                for ($row = 2; $row <= 1001; $row++) {
                    $validation = $event->sheet->getDelegate()->getCell("J$row")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Error de selección');
                    $validation->setError('Por favor, seleccione una categoría de la lista.');
                    $validation->setPromptTitle('Seleccione una categoría');
                    $validation->setPrompt('Elija una categoría de la lista desplegable.');
                    $validation->setFormula1('Categorias!$A$1:$A$' . count($storeCategories));
                }
            }
        ];
    }
}
