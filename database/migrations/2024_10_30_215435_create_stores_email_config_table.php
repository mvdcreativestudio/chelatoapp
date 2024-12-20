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
        Schema::create('stores_email_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('mail_mailer')->default('smtp');
            $table->string('mail_host');
            $table->integer('mail_port');
            $table->string('mail_username');
            $table->string('mail_password');
            $table->string('mail_encryption')->nullable();
            $table->string('mail_from_address');
            $table->string('mail_from_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('stores_email_configs');
    }
};
