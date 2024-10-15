<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log; // Asegúrate de importar el Log
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
    protected $settings;

    public function __construct($categories, $storeId, $settings)
    {
        $this->categories = $categories;
        $this->storeId = $storeId;
        $this->settings = $settings;
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
            Log::info('AfterSheet event executed.');

            // Verificar el valor de categories_has_store en $settings
            Log::info('categories_has_store setting: ' . $this->settings->categories_has_store);

            // Verificar el número de categorías antes de manipularlas
            if ($this->settings->categories_has_store == 1) {
                $storeCategories = $this->categories->where('store_id', $this->storeId)->pluck('name', 'id')->toArray();
                Log::info('Filtrando categorías por store_id: ' . $this->storeId);
            } else {
                $storeCategories = $this->categories->pluck('name', 'id')->toArray();
                Log::info('Tomando todas las categorías, incluidas las de store_id NULL.');
            }

            // Log para ver las categorías obtenidas
            Log::info('Categorías obtenidas en AfterSheet: ', $storeCategories);

            // Crear una hoja oculta para las categorías
            $workbook = $event->sheet->getDelegate()->getParent();
            $categoriesSheet = $workbook->createSheet();
            $categoriesSheet->setTitle('Categorias');

            // Agregar categorías a la hoja oculta
            $row = 1;
            foreach ($storeCategories as $id => $name) {
                Log::info('Agregando categoría a la hoja oculta: ' . $name);  // Log para verificar cada categoría agregada
                $categoriesSheet->setCellValue('A' . $row, $id . '- ' . $name);
                $row++;
            }

            // Ocultar la hoja de categorías
            $categoriesSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
            Log::info('Hoja de categorías creada y oculta.');

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
                $validation->setFormula1('Categorias!$A$1:$A$' . count($storeCategories));  // Verifica el rango de categorías
            }

            Log::info('Dropdown de categorías añadido en las filas.');
        }
    ];
  }

}
