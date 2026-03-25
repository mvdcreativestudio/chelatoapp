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
        if (! Schema::hasTable('orders')) {
            return;
        }

        if (Schema::hasColumn('orders', 'cash_register_log_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('cash_register_log_id')
                ->nullable()
                ->after('document')
                ->constrained('cash_register_logs')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        if (! Schema::hasColumn('orders', 'cash_register_log_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cash_register_log_id']);
            $table->dropColumn('cash_register_log_id');
        });
    }
};
