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
      Schema::create('supplier_order_raw_material', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_order_id')->constrained()->onDelete('cascade');
        $table->foreignId('raw_material_id')->constrained()->onDelete('cascade');
        $table->unsignedInteger('quantity');
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_order_raw_material');
    }
};
