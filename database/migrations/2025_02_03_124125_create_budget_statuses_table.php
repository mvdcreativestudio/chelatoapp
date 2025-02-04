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
        Schema::create('budget_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // soft delete
            $table->enum('status', ['draft', 'pending_approval', 'sent', 'negotiation', 'approved', 'rejected', 'expired', 'cancelled']);
            $table->timestamps();
            $table->softDeletes(); // Agregar soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_statuses');
    }
};


