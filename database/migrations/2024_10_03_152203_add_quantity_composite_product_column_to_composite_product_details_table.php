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
        Schema::table('composite_product_details', function (Blueprint $table) {
            $table->integer('quantity_composite_product')->nullable()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('composite_product_details', function (Blueprint $table) {
            $table->dropColumn('quantity_composite_product');
        });
    }
};