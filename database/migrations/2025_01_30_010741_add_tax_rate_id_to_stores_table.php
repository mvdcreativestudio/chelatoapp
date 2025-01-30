<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTaxRateIdToStoresTable extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('tax_rate_id')
                ->nullable()
                ->after('rut')
                ->constrained('tax_rates')
                ->onDelete('set null');
        });

        // Establecer el valor por defecto después de agregar la columna
        DB::statement("UPDATE stores SET tax_rate_id = 3 WHERE tax_rate_id IS NULL");
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['tax_rate_id']);
            $table->dropColumn('tax_rate_id');
        });
    }
}
