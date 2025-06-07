<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsInOrderStatusChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_status_changes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change(); // Hacer que user_id sea nullable
            $table->string('change_type')->nullable()->change(); // Hacer que change_type sea nullable
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_status_changes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change(); // Revertir user_id a no nullable
            $table->string('change_type')->nullable(false)->change(); // Revertir change_type a no nullable
        });
    }
}
