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
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('can_create_internal_orders_to_all_stores')
                ->default(false)
                ->after('can_receive_internal_orders')
                ->comment('Indicates if the store can create internal orders to all stores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('can_create_internal_orders_to_all_stores');
        });
    }
};
