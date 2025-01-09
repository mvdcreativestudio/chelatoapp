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
        Schema::create('mercado_pago_account_stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('external_id');
            $table->string('street_number');
            $table->string('street_name');
            $table->string('city_name');
            $table->string('state_name');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('reference')->nullable();
            $table->string('store_id')->nullable();
            $table->foreignId('mercado_pago_account_id')->constrained('mercadopago_accounts')->onDelete('cascade');
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
        Schema::dropIfExists('mercado_pago_account_stores');
    }
};
