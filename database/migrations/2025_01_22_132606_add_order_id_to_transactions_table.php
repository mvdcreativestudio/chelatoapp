<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderIdToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si la columna 'order_id' ya existe
        if (!Schema::hasColumn('transactions', 'order_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                // Agregar columna order_id como nullable para evitar problemas al migrar datos existentes
                $table->unsignedBigInteger('order_id')->nullable()->after('id');

                // Crear la relación de clave foránea con orders.id
                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->onDelete('cascade') // Elimina las transacciones si se elimina el pedido relacionado
                    ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Verificar si la columna 'order_id' existe antes de intentar eliminarla
        if (Schema::hasColumn('transactions', 'order_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                // Eliminar la clave foránea y la columna order_id
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            });
        }
    }
}
