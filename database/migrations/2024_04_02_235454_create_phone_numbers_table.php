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
      Schema::create('phone_numbers', function (Blueprint $table) {
        $table->id();
        $table->string('phone_id')->unique();
        $table->string('phone_number');
        $table->boolean('is_store')->default(false);
        $table->string('phone_number_owner')->nullable();
        $table->unsignedBigInteger('store_id')->nullable();
        $table->timestamps();

        $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists('phone_numbers');
    }
};
