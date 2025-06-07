<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveForeignKeysFromPurchaseEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
            // Eliminar la foreign key de raw_material_id
            $table->dropForeign(['raw_material_id']);
            // Eliminar la columna raw_material_id
            $table->dropColumn('raw_material_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
            // Reagregar las columnas
            $table->unsignedBigInteger('raw_material_id');
            $table->unsignedBigInteger('product_id');
            
            // Restaurar las foreign keys
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
}
