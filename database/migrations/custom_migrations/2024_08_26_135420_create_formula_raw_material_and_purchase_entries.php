<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Crear la tabla formula_raw_material
        Schema::create('formula_raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained('formulas')->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained('raw_materials')->onDelete('cascade');
            $table->integer('quantity_required');
            $table->integer('step');
            $table->timestamps();
        });

        // Crear la tabla purchase_entries
        Schema::create('purchase_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained('raw_materials')->onDelete('cascade');
            $table->integer('quantity');
            $table->dateTime('entry_date');
            $table->timestamps();
        });
    }

    public function down(): void {
        // Eliminar las tablas en orden inverso
        Schema::dropIfExists('purchase_entries');
        Schema::dropIfExists('formula_raw_materials');
    }
};
