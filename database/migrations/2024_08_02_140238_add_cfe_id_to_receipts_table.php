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
        Schema::table('receipts', function (Blueprint $table) {
            $table->string('cfeId')->nullable(); // Añade la columna cfeId
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('cfeId');
        });
    }
};
