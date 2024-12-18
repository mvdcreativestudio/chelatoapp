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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->string('last_name'); 
            $table->integer('document'); 
            $table->string('address'); 
            $table->integer('phone'); 
            $table->date('license_date'); 
            $table->date('health_date'); 
            $table->boolean('is_active'); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers');
    }
};
