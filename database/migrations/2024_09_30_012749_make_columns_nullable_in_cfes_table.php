<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cfes', function (Blueprint $table) {
            $table->string('caeNumber')->nullable()->change();
            $table->json('caeRange')->nullable()->change();
            $table->date('caeExpirationDate')->nullable()->change();
            $table->string('sentXmlHash')->nullable()->change();
            $table->string('qrUrl')->nullable()->change();
            $table->string('securityCode')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cfes', function (Blueprint $table) {
            $table->string('caeNumber')->nullable(false)->change();
            $table->json('caeRange')->nullable(false)->change();
            $table->date('caeExpirationDate')->nullable(false)->change();
            $table->string('sentXmlHash')->nullable(false)->change();
            $table->string('qrUrl')->nullable(false)->change();
            $table->string('securityCode')->nullable(false)->change();
        });
    }
};
