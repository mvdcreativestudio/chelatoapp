<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable()->after('cost');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            // Eliminar la columna 'price' si existe
            $table->dropColumn('price');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price', 10, 2);
        });
    }
};
