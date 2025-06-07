<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['raw_material_id']); // Eliminar la clave foránea
            $table->dropColumn('raw_material_id');    // Eliminar la columna
        });
    }

    public function down()
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->unsignedBigInteger('raw_material_id')->nullable();
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('cascade');
        });
    }

};
