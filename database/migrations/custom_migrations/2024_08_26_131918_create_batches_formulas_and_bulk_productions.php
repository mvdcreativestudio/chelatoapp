<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Crear la tabla batches
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number');
            $table->integer('quantity');
            $table->dateTime('production_date');
            $table->foreignId('raw_material_id')->constrained('raw_materials')->onDelete('cascade');
            $table->foreignId('purchase_order_items_id')->constrained('purchase_order_items')->onDelete('cascade');
            $table->dateTime('expiration_date');
            $table->timestamps();
        });

        // Crear la tabla formulas
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('final_product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
        });

        // Crear la tabla adicional (nombre pendiente)
        Schema::create('bulk_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained('formulas')->onDelete('cascade');
            $table->integer('quantity_produced');
            $table->integer('quantity_used');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->dateTime('production_date');
            $table->timestamps();
        });
    }

    public function down(): void {
        // Eliminar las tablas en orden inverso
        Schema::dropIfExists('bulk_productions');
        Schema::dropIfExists('formulas');
        Schema::dropIfExists('batches');
    }
};