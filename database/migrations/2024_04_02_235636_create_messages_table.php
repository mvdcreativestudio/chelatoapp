<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('messages', function (Blueprint $table) {
        $table->id('message_id');
        $table->string('from_phone_id');
        $table->string('to_phone_id');
        $table->text('message_text')->nullable();
        $table->string('message_source');
        $table->dateTime('message_created');
        $table->dateTime('message_updated')->nullable();
        $table->string('message_type');
        $table->string('image_url')->nullable();
        $table->string('audio_url')->nullable();
        $table->string('document_url')->nullable();
        $table->string('video_url')->nullable();
        $table->string('sticker_url')->nullable();
        $table->timestamps();

        $table->foreign('from_phone_id')->references('phone_id')->on('phone_numbers')->onDelete('cascade');
        $table->foreign('to_phone_id')->references('phone_id')->on('phone_numbers')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
