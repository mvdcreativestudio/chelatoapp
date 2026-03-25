<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'concept')) {
                $table->string('concept')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('expenses', 'observations')) {
                $table->text('observations')->nullable()->after('concept');
            }
            if (!Schema::hasColumn('expenses', 'cash_register_log_id')) {
                $table->unsignedBigInteger('cash_register_log_id')->nullable()->after('store_id');
                $table->foreign('cash_register_log_id')
                      ->references('id')
                      ->on('cash_register_logs')
                      ->onDelete('set null');
            }
            if (!Schema::hasColumn('expenses', 'currency')) {
                $table->string('currency')->default('Peso')->after('cash_register_log_id');
            }
            if (!Schema::hasColumn('expenses', 'currency_rate')) {
                $table->decimal('currency_rate', 10, 2)->default(0)->after('currency');
            }
        });

        // Make supplier_id nullable (expenses from cash register may not have supplier)
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'cash_register_log_id')) {
                $table->dropForeign(['cash_register_log_id']);
            }
            $columns = ['concept', 'observations', 'cash_register_log_id', 'currency', 'currency_rate'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('expenses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
