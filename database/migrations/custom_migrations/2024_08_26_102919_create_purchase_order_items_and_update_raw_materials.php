<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Crear la tabla purchase_order_items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_orders_id')->constrained()->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->timestamps();
        });

        // Modificar la tabla raw_materials
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->enum('status', [0, 1, 2])->after('description');
            $table->integer('stock')->after('unit_of_measure');
        });
    }

    public function down(): void {
        // Revertir las modificaciones
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('stock');
        });

        Schema::dropIfExists('purchase_order_items');
    }
};
