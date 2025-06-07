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
        Schema::create('internal_order_items', function (Blueprint $table) {
          $table->id();
          $table->foreignId('internal_order_id')->constrained()->onDelete('cascade');
          $table->foreignId('product_id')->constrained()->onDelete('cascade');
          $table->integer('quantity');
          $table->integer('received_quantity')->nullable(); // si se permite entrega parcial
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_order_items');
    }
};
