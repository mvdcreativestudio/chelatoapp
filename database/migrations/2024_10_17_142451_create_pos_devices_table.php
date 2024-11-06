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
        Schema::create('pos_devices', function (Blueprint $table) {
          $table->id();
          $table->unsignedBigInteger('pos_provider_id'); // ID del proveedor (Scanntech, Fiserv)
          $table->string('identifier'); // Identificador único del aparato POS
          $table->string('company'); // Número de empresa del dispositivo
          $table->string('branch'); // Número de sucursal del dispositivo
          $table->string('user'); // Número de punto de venta del dispositivo
          $table->string('cash_register'); // Número de caja registradora del dispositivo

          $table->timestamps();

          $table->foreign('pos_provider_id')->references('id')->on('pos_providers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_devices');
    }
};
