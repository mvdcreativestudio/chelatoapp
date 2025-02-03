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
        Schema::table('composite_products', function (Blueprint $table) {
            $table->enum('currency', ['Peso', 'Dólar'])->after('description');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('composite_products', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
