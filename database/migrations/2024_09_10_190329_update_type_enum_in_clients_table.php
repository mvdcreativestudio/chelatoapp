<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTypeEnumInClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modificar la columna 'type' para incluir el nuevo valor 'no-client'
        DB::statement("ALTER TABLE clients MODIFY COLUMN type ENUM('company', 'individual', 'no-client') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir la modificación eliminando el valor 'no-client' del enum
        DB::statement("ALTER TABLE clients MODIFY COLUMN type ENUM('company', 'individual') NOT NULL");
    }
}
