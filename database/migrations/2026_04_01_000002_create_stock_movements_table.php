<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('product_type'); // App\Models\Product o App\Models\CompositeProduct
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type'); // manual, sale, order_delete
            $table->integer('quantity'); // positivo o negativo
            $table->integer('old_stock');
            $table->integer('new_stock');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['product_id', 'product_type']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
