<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pos_devices', function (Blueprint $table) {
            // Modificar las columnas 'user' y 'cash_register' para que sean nullables
            $table->string('user')->nullable()->change();
            $table->string('cash_register')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_devices', function (Blueprint $table) {
            // Revertir las columnas 'user' y 'cash_register' a no nullable si se hace rollback
            $table->string('user')->nullable(false)->change();
            $table->string('cash_register')->nullable(false)->change();
        });
    }
};
