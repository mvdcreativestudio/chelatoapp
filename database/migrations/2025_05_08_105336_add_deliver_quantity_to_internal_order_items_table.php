<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('internal_order_items', function (Blueprint $table) {
            $table->integer('deliver_quantity')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('internal_order_items', function (Blueprint $table) {
            $table->dropColumn('deliver_quantity');
        });
    }
};
