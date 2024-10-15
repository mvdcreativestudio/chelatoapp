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
        Schema::table('composite_products', function (Blueprint $table) {
            $table->string('image')->nullable()->default('assets/img/ecommerce-images/placeholder.png')->after('recommended_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('composite_products', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
