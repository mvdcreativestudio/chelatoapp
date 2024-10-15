<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entry_details', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount_debit', 15, 2)->default(0);
            $table->decimal('amount_credit', 15, 2)->default(0);
            $table->foreignId('entry_id')->constrained('entries');
            $table->foreignId('entry_account_id')->constrained('entry_accounts');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_details');
    }
};
