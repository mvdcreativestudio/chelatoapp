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
        Schema::create('catalogue_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->boolean('display_catalogue')->default(true);
            $table->string('email');
            $table->string('phone_code');
            $table->string('phone');
            $table->string('logo')->nullable();
            $table->boolean('show_whatsapp_button')->default(true);
            $table->string('header_image')->nullable();
            $table->boolean('allow_share')->default(false);
            $table->timestamps();

            
            // Si tienes la tabla stores, puedes añadir la relación de clave foránea
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_settings');
    }
};
