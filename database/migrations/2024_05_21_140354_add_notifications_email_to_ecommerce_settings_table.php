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
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->string('notifications_email')->nullable()->after('enable_coupons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->dropColumn('notifications_email');
        });
    }
};
