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
      Schema::create('supplier_orders', function (Blueprint $table) {
          $table->id();
          $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
          $table->date('order_date');
          $table->string('shipping_status')->default('pending');
          $table->string('payment_status')->default('pending');
          $table->string('payment')->default('0.00');
          $table->text('notes')->nullable();
          $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_orders');
    }
};
