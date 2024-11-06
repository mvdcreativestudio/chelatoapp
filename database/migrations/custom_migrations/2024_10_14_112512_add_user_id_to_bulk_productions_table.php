<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToBulkProductionsTable extends Migration
{
    public function up()
    {
        Schema::table('bulk_productions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('production_date'); // Añade la columna user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Define la FK
        });
    }

    public function down()
    {
        Schema::table('bulk_productions', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // Elimina la FK
            $table->dropColumn('user_id'); // Elimina la columna
        });
    }
}
