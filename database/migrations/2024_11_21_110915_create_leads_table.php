<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('user_creator_id')->nullable(); 
            $table->unsignedBigInteger('store_id')->nullable(); 
            $table->integer('column_id')->nullable(); 
            $table->integer('client_id')->nullable(); 
            $table->boolean('archived')->nullable(); 
            $table->string('name'); 
            $table->string('description')->nullable(); 
            $table->decimal('amount_of_money', 15, 2)->nullable();
            $table->integer('category_id')->nullable(); 
            $table->string('phone')->nullable(); 
            $table->string('email')->nullable(); 
            $table->integer('position')->nullable(); 
            $table->timestamps();
            
            $table->foreign('user_creator_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('leads');
    }
};
