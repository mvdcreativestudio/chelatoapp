<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_register_pos_device', function (Blueprint $table) {
          $table->id();
          $table->unsignedBigInteger('cash_register_id');  // ID de la caja registradora
          $table->unsignedBigInteger('pos_device_id');     // ID del dispositivo POS asociado
          $table->timestamps();

          $table->foreign('cash_register_id')->references('id')->on('cash_registers');
          $table->foreign('pos_device_id')->references('id')->on('pos_devices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_pos_device');
    }
};
