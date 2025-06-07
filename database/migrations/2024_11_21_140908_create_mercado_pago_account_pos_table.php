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
        Schema::create('mercado_pago_account_pos', function (Blueprint $table) {
            $table->id();
            $table->string('id_pos');
            $table->string('name');
            $table->boolean('fixed_amount')->default(false);
            $table->integer('category')->nullable();
            $table->text('qr_image');
            $table->text('template_document');
            $table->text('template_image');
            $table->text('qr_code');
            $table->string('store_id');
            $table->string('external_store_id');
            $table->string('external_id');
            $table->foreignId('mercado_pago_account_store_id')
                  ->constrained('mercado_pago_account_stores')
                  ->onDelete('cascade');
            $table->foreignId('cash_register_id')
                  ->constrained('cash_registers')
                  ->onDelete('cascade');
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
        Schema::dropIfExists('mercado_pago_account_pos');
    }
};
