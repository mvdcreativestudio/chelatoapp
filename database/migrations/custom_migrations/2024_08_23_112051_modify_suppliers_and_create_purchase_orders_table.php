<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Eliminar la columna 'store_id' de la tabla 'suppliers'
        Schema::table('suppliers', function (Blueprint $table) {
            // Eliminar la clave foránea primero
            $table->dropForeign(['store_id']);
            // Ahora, eliminar la columna
            $table->dropColumn('store_id');
        });

        // Crear la tabla 'purchase_orders'
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->enum('status', ['0', '1', '2']);
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir la eliminación de la columna 'store_id'
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable(); // Asegúrate de añadir esta columna correctamente según la definición anterior.
        });

        // Eliminar la tabla 'purchase_orders'
        Schema::dropIfExists('purchase_orders');
    }
};
