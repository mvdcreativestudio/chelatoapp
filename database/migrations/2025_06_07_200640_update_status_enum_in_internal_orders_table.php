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
        DB::statement("ALTER TABLE internal_orders MODIFY status ENUM('pending', 'accepted', 'delivered', 'cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE internal_orders MODIFY status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending'");
    }

};
