<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatch_note', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('order_id'); 
            $table->unsignedBigInteger('product_id');
            $table->date('date'); 
            $table->unsignedBigInteger('quantity'); 
            $table->enum('bombing_type', ['Drag', 'Throw', 'Not applicable']); 
            $table->enum('delivery_method', ['Dumped', 'Pumped']); 
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatch_note');
    }
}
