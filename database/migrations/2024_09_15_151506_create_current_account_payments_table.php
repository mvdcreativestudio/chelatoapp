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
        Schema::create('current_account_payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('payment_amount', 10, 2);
            $table->date('payment_date');
            $table->foreignId('current_account_id')->constrained('current_accounts')->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onUpdate('restrict')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_account_payments');
    }
};
