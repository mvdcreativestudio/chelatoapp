<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First set all existing category_id to null
        DB::table('leads')->update(['category_id' => null]);

        Schema::table('leads', function (Blueprint $table) {
            // Then modify the column type
            $table->unsignedBigInteger('category_id')->nullable()->change();
            
            // Finally add the foreign key
            $table->foreign('category_id')
                  ->references('id')
                  ->on('lead_categories')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            // First drop the foreign key
            $table->dropForeign(['category_id']);
            // Then change the column back
            $table->integer('category_id')->nullable()->change();
        });
    }
};
