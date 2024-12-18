<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoteDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('note_delivery', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('dispatch_note_id');
            $table->dateTime('departuring')->nullable();
            $table->dateTime('arriving')->nullable();
            $table->dateTime('unload_starting')->nullable();
            $table->dateTime('unload_finishing')->nullable();
            $table->dateTime('departure_from_site')->nullable();
            $table->dateTime('return_to_plant')->nullable();
            
            $table->timestamps(); 

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('dispatch_note_id')->references('id')->on('dispatch_note')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('note_delivery');
    }
}
