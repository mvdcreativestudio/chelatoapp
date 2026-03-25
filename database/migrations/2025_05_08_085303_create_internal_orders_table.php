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
        Schema::create('internal_orders', function (Blueprint $table) {
          $table->id();
          $table->foreignId('from_store_id')->constrained('stores');
          $table->foreignId('to_store_id')->constrained('stores');
          $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
          $table->foreignId('created_by')->constrained('users');
          $table->timestamp('delivery_date')->nullable();
          $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_orders');
    }
};
