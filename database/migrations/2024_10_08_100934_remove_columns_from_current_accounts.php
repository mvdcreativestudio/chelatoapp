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
        Schema::table('current_accounts', function (Blueprint $table) {
            $table->dropColumn('voucher');
            $table->dropColumn('transaction_date');
            $table->dropColumn('due_date');
            $table->dropColumn('total_debit');
            $table->dropForeign(['current_account_settings_id']);
            $table->dropColumn('current_account_settings_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
