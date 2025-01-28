<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNullableColumnsInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('phone', 255)->nullable()->change();
            $table->string('address', 255)->nullable()->change();
            $table->string('city', 255)->nullable()->change();
            $table->string('state', 255)->nullable()->change();
            $table->string('country', 255)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->integer('doc_number')->nullable()->change();
            $table->integer('default_payment_method')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('phone', 255)->nullable(false)->change();
            $table->string('address', 255)->nullable(false)->change();
            $table->string('city', 255)->nullable(false)->change();
            $table->string('state', 255)->nullable(false)->change();
            $table->string('country', 255)->nullable(false)->change();
            $table->string('email', 255)->nullable(false)->change();
            $table->integer('doc_number')->nullable(false)->change();
            $table->integer('default_payment_method')->nullable(false)->change();
        });
    }
}
