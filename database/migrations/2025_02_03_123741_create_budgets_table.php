<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained('leads'); // soft delete
            $table->foreignId('order_id')->nullable()->constrained('orders'); // soft delete
            $table->foreignId('price_list_id')->nullable()->constrained('price_lists'); // soft delete
            $table->foreignId('store_id')->nullable()->constrained('stores'); // soft delete
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('discount_type', ['Percentage', 'Fixed'])->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
            $table->softDeletes(); // Agregar soft deletes
        });
    }

    public function down()
    {
        Schema::dropIfExists('budgets');
    }
};