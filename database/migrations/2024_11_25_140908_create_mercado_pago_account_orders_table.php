<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mercado_pago_account_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // Relación con la tabla orders
            $table->string('payment_id')->unique(); // ID del pago de MercadoPago
            $table->string('status')->nullable(); // Estado del pago (approved, refunded, etc.)
            $table->decimal('amount', 10, 2); // Monto del pago
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
        Schema::dropIfExists('mercado_pago_account_orders');
    }
};
