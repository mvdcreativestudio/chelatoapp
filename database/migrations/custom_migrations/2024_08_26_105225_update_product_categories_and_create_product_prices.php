<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Modificar la tabla product_categories
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropColumn('status');
        });

        // Crear la tabla product_prices
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('currency');
            $table->decimal('price', 8, 2);
            $table->dateTime('effective_date'); // Cambiado a datetime
            $table->timestamps();
        });
    }

    public function down(): void {
        // Revertir las modificaciones en product_categories
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->enum('status', [0, 1, 2])->default(0);
        });

        // Eliminar la tabla product_prices
        Schema::dropIfExists('product_prices');
    }
};
