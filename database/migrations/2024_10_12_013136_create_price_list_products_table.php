<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceListProductsTable extends Migration
{
    public function up()
    {
        Schema::create('price_list_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            // Relaciones
            $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_list_products');
    }
}
