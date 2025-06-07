<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leads_id');
            $table->string('description')->nullable();;
            $table->enum('status', [0, 1])->nullable();;
            $table->enum('priority', [0, 1, 2, 3])->nullable();;
            $table->datetime('due_date')->nullable();
            $table->timestamps();

            $table->foreign('leads_id')->references('id')->on('leads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_tasks');
    }
}
