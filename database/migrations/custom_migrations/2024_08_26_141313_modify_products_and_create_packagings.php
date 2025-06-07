<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        // Crear la tabla packagings
        Schema::create('packagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_production_id')->constrained('bulk_productions')->onDelete('cascade');
            $table->integer('quantity_packaged');
            $table->dateTime('packaging_date');
            $table->foreignId('final_product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        // Eliminar la tabla packagings
        Schema::dropIfExists('packagings');
    }
};
