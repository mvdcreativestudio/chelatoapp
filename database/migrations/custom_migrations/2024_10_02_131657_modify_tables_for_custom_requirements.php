<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTablesForCustomRequirements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Modificar la tabla formulas
        Schema::table('formulas', function (Blueprint $table) {
            $table->string('unit_of_measure', 20)->after('final_product_id');
            $table->decimal('quantity', 10, 2)->after('unit_of_measure');
        });

        // 2. Eliminar FK y columna final_product_id de packagings
        Schema::table('packagings', function (Blueprint $table) {
            $table->dropForeign(['final_product_id']); // Eliminar FK
            $table->dropColumn('final_product_id'); // Eliminar columna
        });

        // 3. Crear tabla packages
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_sellable'); // Es vendible
            $table->decimal('cost', 10, 2)->nullable(); // Precio de compra
            $table->decimal('price', 10, 2)->nullable(); // Precio de venta
            $table->string('unit_of_measure', 20); // Unidad de medida
            $table->decimal('size', 10, 2); // Tamaño
            $table->integer('stock'); // Stock
            $table->timestamps();
        });

        // 4. Modificar tabla packagings, agregar package_id como FK
        Schema::table('packagings', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->after('quantity_packaged');

            // Agregar la FK a la tabla packages
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir los cambios
        Schema::table('formulas', function (Blueprint $table) {
            $table->dropColumn('unit_of_measure');
            $table->dropColumn('quantity');
        });

        Schema::table('packagings', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
            $table->unsignedBigInteger('final_product_id'); // Volver a agregar final_product_id
            $table->foreign('final_product_id')->references('id')->on('final_products'); // Volver a agregar la FK
        });

        Schema::dropIfExists('packages');
    }
}
