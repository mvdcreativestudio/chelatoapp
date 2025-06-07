<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCompanyAndBranchFromPosDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Eliminar las columnas `company` y `branch`
        Schema::table('pos_devices', function (Blueprint $table) {
            if (Schema::hasColumn('pos_devices', 'company')) {
                $table->dropColumn('company');
            }

            if (Schema::hasColumn('pos_devices', 'branch')) {
                $table->dropColumn('branch');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restaurar las columnas `company` y `branch`
        Schema::table('pos_devices', function (Blueprint $table) {
            $table->string('company')->nullable();
            $table->string('branch')->nullable();
        });
    }
}
