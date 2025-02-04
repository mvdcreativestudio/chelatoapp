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
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products'); // soft delete
            $table->unsignedBigInteger('quantity');
            $table->decimal('price', 10, 2);
            $table->enum('discount_type', ['Percentage', 'Fixed'])->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes(); // Agregar soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};