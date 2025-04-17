<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {

            // Eliminar foreign key y columna currency_id
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');

            // Agregar columna currency (enum) después de income_category_id
            $table->enum('currency', ['Dólar', 'Peso'])->after('income_category_id');

            // Agregar columna currency_rate después de currency
            $table->decimal('currency_rate', 10, 5)->after('currency');

            // Agregar columna tax_rate_id después de currency_rate
            $table->unsignedBigInteger('tax_rate_id')->nullable()->after('currency_rate');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');

            // Agregar columna cash_register_log_id después de income_category_id (original)
            $table->unsignedBigInteger('cash_register_log_id')->nullable()->after('income_category_id');
            $table->foreign('cash_register_log_id')->references('id')->on('cash_register_logs')->onDelete('set null');

            $table->json('items')->nullable()->after('supplier_id');

        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {

            // Restaurar columna currency_id
            $table->unsignedBigInteger('currency_id')->nullable()->after('income_category_id');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            // Eliminar nuevas columnas
            $table->dropForeign(['cash_register_log_id']);
            $table->dropColumn('cash_register_log_id');

            $table->dropColumn('currency');
            $table->dropColumn('currency_rate');

            $table->dropForeign(['tax_rate_id']);
            $table->dropColumn('tax_rate_id');

            $table->dropColumn('items');
        });
    }
};