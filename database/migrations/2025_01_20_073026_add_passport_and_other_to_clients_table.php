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
            $table->string('passport')->nullable()->after('ci'); // Agregar columna passport
            $table->string('other_id_type')->nullable()->after('passport'); // Agregar columna other_id_type
        });
    }

    /**
      * Reverse the migrations.
    */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['passport', 'other_id_type']);
        });
    }

};
