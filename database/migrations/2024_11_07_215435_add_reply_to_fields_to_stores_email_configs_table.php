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
        Schema::table('stores_email_configs', function (Blueprint $table) {
            $table->string('mail_reply_to_address')->nullable()->after('mail_from_name');
            $table->string('mail_reply_to_name')->nullable()->after('mail_reply_to_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('stores_email_configs', function (Blueprint $table) {
            $table->dropColumn(['mail_reply_to_address', 'mail_reply_to_name']);
        });
    }
};
