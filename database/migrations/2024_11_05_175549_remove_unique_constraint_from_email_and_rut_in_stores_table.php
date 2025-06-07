<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueConstraintFromEmailAndRutInStoresTable extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropUnique('stores_email_unique'); // Elimina la restricción única en 'email'
            $table->dropUnique('stores_rut_unique');   // Elimina la restricción única en 'rut'
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->unique('email'); // Vuelve a añadir la restricción única en 'email'
            $table->unique('rut');   // Vuelve a añadir la restricción única en 'rut'
        });
    }
}
