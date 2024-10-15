<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, WithBatchInserts
{
    use SkipsErrors, SkipsFailures;

    protected $storeId;
    protected $categoryCache = [];
    protected $rowCount = 0;

    public function __construct($storeId)
    {
        $this->storeId = $storeId;
        $this->loadCategories();
    }

    private function loadCategories()
    {
        $categories = ProductCategory::where('store_id', $this->storeId)->get();
        foreach ($categories as $category) {
            $this->categoryCache[$category->id] = $category->id;
        }
    }

    public function model(array $row)
    {
        if ($this->isEmptyRow($row)) {
            return null;
        }

        $this->rowCount++;
        Log::info("Procesando fila {$this->rowCount}:", $row);

        $product = new Product([
            'name' => $row['nombre'],
            'sku' => $row['sku'] ?? null,
            'description' => $row['descripcion'] ?? null,
            'type' => $row['tipo'] ?? 'simple',
            'old_price' => floatval($row['precio']),
            'price' => isset($row['precio']) ? floatval($row['precio_oferta']) : null,
            'stock' => isset($row['stock']) ? intval($row['stock']) : 0,
            'store_id' => $this->storeId,
            'image' => $row['imagen'] ?? '/assets/img/ecommerce-images/placeholder.png',
            'status' => isset($row['estado']) ? (in_array(strtolower($row['estado']), ['sí', 'si', 'activo']) ? 1 : 0) : 1,
            'draft' => isset($row['borrador']) ? (in_array(strtolower($row['borrador']), ['sí', 'si']) ? 1 : 0) : 0,
            'safety_margin' => isset($row['margen_seguridad']) ? floatval($row['margen_seguridad']) : 0,
        ]);

        $product->save();

        if (!empty($row['categoria'])) {
            $this->assignCategory($product, $row['categoria']);
        }

        Log::info("Producto importado:", $product->toArray());

        return $product;
    }

    private function isEmptyRow($row): bool
    {
        return empty($row['nombre']) || empty($row['precio']);
    }

    private function assignCategory($product, $categoryInput)
    {
        $categoryId = intval($categoryInput);
        if (isset($this->categoryCache[$categoryId])) {
            $product->categories()->sync([$categoryId]);
            Log::info("Categoría asignada al producto {$product->id}: ID {$categoryId}");
        } else {
            Log::warning("Categoría no encontrada: ID {$categoryId} para el producto: {$product->name}");
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', Rule::in(['simple', 'variable'])],
            'precio' => ['required', 'numeric', 'min:0'],
            'precio_oferta' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'imagen' => ['nullable', 'string'],
            'estado' => ['nullable', 'string', Rule::in(['sí', 'si', 'no', 'activo', 'inactivo'])],
            'borrador' => ['nullable', 'string', Rule::in(['sí', 'si', 'no'])],
            'margen_seguridad' => ['nullable', 'numeric', 'min:0'],
            'categoria' => ['nullable', 'string', 'regex:/^\d+-.+$/'],
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function __destruct()
    {
        // Asegurarse de guardar el último producto
        $this->saveCurrentProduct();
    }

}
