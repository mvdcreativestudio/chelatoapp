<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePosOrdersTableVarcharNotesAndClientId  extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            // Renombrar la columna 'client_type' a 'client_id'
            if (Schema::hasColumn('pos_orders', 'client_type')) {
                $table->renameColumn('client_type', 'client_id');
            }
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            // Crear columna notes
            $table->string('notes', 255)->nullable()->after('total');

            // Asegurarse de que 'client_id' sea una clave foránea que referencie el 'id' de 'clients'
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            // Revertir el cambio de 'notes' a TEXT
            $table->text('notes')->nullable()->change();

            // Revertir el cambio de nombre 'client_id' a 'client_type'
            $table->dropForeign(['client_id']);
            $table->renameColumn('client_id', 'client_type');
        });
    }
}
