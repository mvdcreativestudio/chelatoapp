<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_register_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_register_logs', 'name')) {
                $table->string('name')->nullable()->after('cash_register_id');
            }
            if (!Schema::hasColumn('cash_register_logs', 'mercadopago_sales')) {
                $table->decimal('mercadopago_sales', 12, 2)->default(0)->after('pos_sales');
            }
            if (!Schema::hasColumn('cash_register_logs', 'bank_transfer_sales')) {
                $table->decimal('bank_transfer_sales', 12, 2)->default(0)->after('mercadopago_sales');
            }
            if (!Schema::hasColumn('cash_register_logs', 'internal_credit_sales')) {
                $table->decimal('internal_credit_sales', 12, 2)->default(0)->after('bank_transfer_sales');
            }
            if (!Schema::hasColumn('cash_register_logs', 'cash_expenses')) {
                $table->decimal('cash_expenses', 12, 2)->default(0)->after('cash_float');
            }
            if (!Schema::hasColumn('cash_register_logs', 'actual_cash')) {
                $table->decimal('actual_cash', 12, 2)->nullable()->after('cash_expenses');
            }
            if (!Schema::hasColumn('cash_register_logs', 'cash_difference')) {
                $table->decimal('cash_difference', 12, 2)->default(0)->after('actual_cash');
            }
        });

        // Change cash_sales, pos_sales, cash_float to decimal for precision
        Schema::table('cash_register_logs', function (Blueprint $table) {
            $table->decimal('cash_sales', 12, 2)->default(0)->change();
            $table->decimal('pos_sales', 12, 2)->default(0)->change();
            $table->decimal('cash_float', 12, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cash_register_logs', function (Blueprint $table) {
            $columns = ['name', 'mercadopago_sales', 'bank_transfer_sales', 'internal_credit_sales', 'cash_expenses', 'actual_cash', 'cash_difference'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('cash_register_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
