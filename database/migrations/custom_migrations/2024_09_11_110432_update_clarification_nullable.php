<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('formula_raw_materials', function (Blueprint $table) {
            // Hacer que la columna clarification sea nullable
            $table->string('clarification', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('formula_raw_materials', function (Blueprint $table) {
            // Revertir el cambio y hacer que clarification no sea nullable
            $table->string('clarification', 255)->nullable(false)->change();
        });
    }

};
