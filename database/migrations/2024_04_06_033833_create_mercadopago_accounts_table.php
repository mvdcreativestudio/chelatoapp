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
        Schema::create('mercadopago_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('public_key');
            $table->string('access_token');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mercadopago_accounts');
    }
};
