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
        Schema::create('current_account_initial_credits', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_debit', 10, 2);
            $table->string('description')->nullable();
            $table->date('due_date');
            $table->foreignId('current_account_id')->constrained()->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('current_account_settings_id')->constrained('current_account_settings', 'id')->onUpdate('restrict')->onDelete('restrict')->index('ca_initial_credits_settings_id_foreign');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_account_initial_credits');
    }
};
