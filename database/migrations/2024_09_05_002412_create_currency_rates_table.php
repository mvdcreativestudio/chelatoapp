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
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la moneda
            $table->date('date'); // Fecha del tipo de cambio
            $table->decimal('buy', 15, 5)->nullable(); // Valor de compra con 5 decimales
            $table->decimal('sell', 15, 5)->nullable(); // Valor de venta con 5 decimales
            $table->timestamps();

            // Clave única para evitar duplicados por nombre y fecha
            $table->unique(['name', 'date']);
        });
    }

    /**
     * Reverse the migrations.
    */
    public function down()
    {
        Schema::dropIfExists('currency_rates');
    }
};
