<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
    */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('doc_type')->nullable(); // Tipo de documento receptor (2 para RUC, 3 para CI)
            $table->string('document')->nullable(); // Documento receptor (RUC o CI)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
      Schema::table('clients', function (Blueprint $table) {
          $table->dropColumn('doc_type');
          $table->dropColumn('document');
      });
    }
};
