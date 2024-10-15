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
        // Cambia la columna `shipping_status` para que tenga las nuevas opciones
        Schema::table('orders', function (Blueprint $table) {
            // Primero debe eliminar la columna antigua, idealmente deberías hacer una copia de seguridad de estos datos si son importantes.
            $table->dropColumn('shipping_status');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Ahora agrega la columna con las nuevas opciones
            $table->enum('shipping_status', ['pending', 'shipped', 'delivered', 'cancelled'])->default('pending')->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn('shipping_status');
      });

      Schema::table('orders', function (Blueprint $table) {
        $table->enum('shipping_status', ['pending', 'processing', 'shipped', 'delivered'])->default('pending');
      });
    }
};
