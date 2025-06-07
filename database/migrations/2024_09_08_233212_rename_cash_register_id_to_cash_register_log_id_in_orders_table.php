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
        Schema::table('orders', function (Blueprint $table) {
            // Eliminar la restricción de clave foránea existente en 'cash_register_id'
            $table->dropForeign(['cash_register_id']);

            // Renombrar la columna 'cash_register_id' a 'cash_register_log_id'
            $table->renameColumn('cash_register_id', 'cash_register_log_id');

            // Crear la nueva relación de clave foránea con 'cash_register_logs'
            $table->foreign('cash_register_log_id')
                ->references('id')
                ->on('cash_register_logs')
                ->onDelete('set null'); // Cambia 'set null' según tu política deseada (restrict, cascade, etc.)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Eliminar la restricción de clave foránea en 'cash_register_log_id'
            $table->dropForeign(['cash_register_log_id']);

            // Renombrar la columna 'cash_register_log_id' de vuelta a 'cash_register_id'
            $table->renameColumn('cash_register_log_id', 'cash_register_id');

            // Restaurar la relación de clave foránea original
            $table->foreign('cash_register_id')
                ->references('id')
                ->on('cash_registers')
                ->onDelete('set null'); // Cambia 'set null' según tu política original
        });
    }
};