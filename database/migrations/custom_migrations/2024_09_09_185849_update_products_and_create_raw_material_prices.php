<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductsAndCreateRawMaterialPrices extends Migration
{
    public function up()
    {
        // Crear la tabla raw_material_prices
        Schema::create('raw_material_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('raw_material_id');
            $table->string('currency');
            $table->decimal('price', 8, 2);
            $table->timestamps();

            // FK hacia la tabla raw_materials
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('cascade');
        });
    }

    public function down()
    {

        // Eliminar la tabla raw_material_prices
        Schema::dropIfExists('raw_material_prices');
    }
}
