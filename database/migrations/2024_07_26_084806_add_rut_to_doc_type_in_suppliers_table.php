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
        Schema::table('suppliers', function (Blueprint $table) {
            DB::statement("ALTER TABLE suppliers MODIFY COLUMN doc_type ENUM('DNI', 'PASSPORT', 'OTHER', 'RUT')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Revert to the original enum values without "RUT"
            DB::statement("ALTER TABLE suppliers MODIFY COLUMN doc_type ENUM('DNI', 'PASSPORT', 'OTHER')");
        });
    }
};
