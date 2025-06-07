<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyUnitOfMeasureToEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cambiar la columna unit_of_measure a enum en la tabla formulas
        Schema::table('formulas', function (Blueprint $table) {
            // Eliminar la columna actual y luego agregarla como enum
            DB::statement("ALTER TABLE formulas MODIFY unit_of_measure ENUM('L', 'ml') AFTER final_product_id");
        });

        // Cambiar la columna unit_of_measure a enum en la tabla packages
        Schema::table('packages', function (Blueprint $table) {
            // Eliminar la columna actual y luego agregarla como enum
            DB::statement("ALTER TABLE packages MODIFY unit_of_measure ENUM('L', 'ml') AFTER price");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el cambio de la columna unit_of_measure a varchar en formulas
        Schema::table('formulas', function (Blueprint $table) {
            DB::statement("ALTER TABLE formulas MODIFY unit_of_measure VARCHAR(20) AFTER final_product_id");
        });

        // Revertir el cambio de la columna unit_of_measure a varchar en packages
        Schema::table('packages', function (Blueprint $table) {
            DB::statement("ALTER TABLE packages MODIFY unit_of_measure VARCHAR(20) AFTER price");
        });
    }
}
