<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientPriceListsTable extends Migration
{
    public function up()
    {
        Schema::create('client_price_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('price_list_id');
            $table->timestamps();

            // Relaciones
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_price_lists');
    }
}
