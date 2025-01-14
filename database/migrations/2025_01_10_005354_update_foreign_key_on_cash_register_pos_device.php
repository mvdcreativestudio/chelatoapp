<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeyOnCashRegisterPosDevice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_register_pos_device', function (Blueprint $table) {
            // Primero eliminamos la clave foránea existente
            $table->dropForeign('cash_register_pos_device_pos_device_id_foreign');

            // Luego agregamos una nueva clave foránea con ON DELETE CASCADE
            $table->foreign('pos_device_id')
                ->references('id')->on('pos_devices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_register_pos_device', function (Blueprint $table) {
            // Restaurar la clave foránea sin ON DELETE CASCADE
            $table->dropForeign('cash_register_pos_device_pos_device_id_foreign');

            $table->foreign('pos_device_id')
                ->references('id')->on('pos_devices')
                ->onDelete('restrict'); // Esto es lo que estaba configurado originalmente
        });
    }
}
