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
        // Verificar si la tabla 'currency_rates' no existe antes de crearla
        if (!Schema::hasTable('currency_rates')) {
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
    }

    /**
     * Reverse the migrations.
    */
    public function down()
    {
        // Eliminar la tabla solo si existe
        if (Schema::hasTable('currency_rates')) {
            Schema::dropIfExists('currency_rates');
        }
    }
};
