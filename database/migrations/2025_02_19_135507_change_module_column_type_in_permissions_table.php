<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeModuleColumnTypeInPermissionsTable extends Migration
{
    /**
     * Ejecutar la migración: cambiar la columna `module` de enum a string
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('module', 100)->default('general')->change();
        });
    }

    /**
     * Revertir la migración: volver la columna `module` a enum
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->enum('module', [
                'ecommerce',
                'accounting',
                'billing',
                'manufacturing',
                'point-of-sale',
                'crm',
                'marketing',
                'datacenter',
                'stock',
                'general',
                'management',
                'expenses'
            ])->default('general')->change();
        });
    }
}
