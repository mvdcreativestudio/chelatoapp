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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('client_id')->constrained();
            $table->foreignId('product_id')->constrained('products', 'id');
            $table->foreignId('store_id')->constrained();
            $table->integer('subtotal');
            $table->integer('tax')->nullable();
            $table->integer('shipping');
            $table->foreignId('coupon_id')->constrained();
            $table->integer('coupon_amount')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('total');
            $table->enum('payment_status', ['pending', 'paid', 'failed']);
            $table->enum('shipping_status', ['pending', 'processing', 'shipped', 'delivered']);
            $table->string('payment_method');
            $table->string('shipping_method');
            $table->string('shipping_tracking');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
