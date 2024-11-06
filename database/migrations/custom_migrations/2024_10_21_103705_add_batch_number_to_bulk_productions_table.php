<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchNumberToBulkProductionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bulk_productions', function (Blueprint $table) {
            $table->string('batch_number')->unique()->after('id');
        });
    }
    
    public function down()
    {
        Schema::table('bulk_productions', function (Blueprint $table) {
            $table->dropColumn('batch_number');
        });
    }
    
};