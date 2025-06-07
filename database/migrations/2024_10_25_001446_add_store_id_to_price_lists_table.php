<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_lists', function (Blueprint $table) {
            // Agregar la columna store_id
            $table->unsignedBigInteger('store_id')->nullable()->after('id');

            // Crear la clave foránea
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_lists', function (Blueprint $table) {
            // Eliminar la clave foránea y la columna
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
