<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateForeignKeyOnOrderStatusChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Deshabilitar restricciones de clave foránea temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('order_status_changes', function (Blueprint $table) {
            // Verificar si la columna 'order_id' existe antes de agregar la clave foránea
            if (Schema::hasColumn('order_status_changes', 'order_id')) {
                // Crear la nueva relación de clave foránea con eliminación en cascada
                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->onDelete('cascade');
            }
        });

        // Volver a habilitar las restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Deshabilitar restricciones de clave foránea temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('order_status_changes', function (Blueprint $table) {
            // Verificar si la columna 'order_id' existe antes de intentar eliminar la clave foránea
            if (Schema::hasColumn('order_status_changes', 'order_id')) {
                $table->dropForeign(['order_id']);
            }

            // Restaurar la clave foránea sin la opción de eliminación en cascada
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('restrict');
        });

        // Volver a habilitar las restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
