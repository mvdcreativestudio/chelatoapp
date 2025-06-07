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
        Schema::table('stores', function (Blueprint $table) {
            $table->unsignedBigInteger('pos_provider_id')->nullable()->after('peya_envios_key');
            $table->foreign('pos_provider_id')->references('id')->on('pos_providers')->onDelete('set null'); // Relación con la tabla de proveedores POS
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['pos_provider_id']);
            $table->dropColumn('pos_provider_id');
        });
    }

};
