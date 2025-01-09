<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_conversations', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('lead_id');
            $table->text('message'); 
            $table->unsignedBigInteger('user_id'); 
            $table->boolean('is_deleted')->default(false);
            $table->timestamps(); 

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_conversations');
    }
}
