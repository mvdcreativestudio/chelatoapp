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
        Schema::table('pos_integrations_store_info', function (Blueprint $table) {
            // Agregar nueva columna system_id
            $table->string('system_id')->nullable()->after('branch');
            
            // Modificar las columnas company y branch para que sean nullables
            $table->string('company')->nullable()->change();
            $table->string('branch')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_integrations_store_info', function (Blueprint $table) {
            // Eliminar las columna system_id
            $table->dropColumn('system_id');

            // Revertir el cambio de las columnas company y branch a no-nullable (si es necesario)
            $table->string('company')->nullable(false)->change();
            $table->string('branch')->nullable(false)->change();
        });
    }
};
