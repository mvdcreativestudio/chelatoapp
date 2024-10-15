<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsToFloatInOrdersAndProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cambiar columnas en la tabla orders
        Schema::table('orders', function (Blueprint $table) {
            $table->double('subtotal')->change();
            $table->double('total')->change();
            $table->double('tax')->change();
            $table->double('shipping')->change();
            $table->double('discount')->change();
        });

        // Cambiar columnas en la tabla products
        Schema::table('products', function (Blueprint $table) {
            $table->double('price')->change();
            $table->double('old_price')->change();
            $table->double('discount')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir los cambios en la tabla orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->change();
            $table->decimal('total', 10, 2)->change();
            $table->decimal('tax', 10, 2)->change();
            $table->decimal('shipping', 10, 2)->change();
            $table->decimal('discount', 10, 2)->change();
        });

        // Revertir los cambios en la tabla products
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
            $table->decimal('old_price', 10, 2)->change();
            $table->decimal('discount', 10, 2)->change();
        });
    }
}
