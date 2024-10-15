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
        Schema::table('cfes', function (Blueprint $table) {
            // Verificar si la columna 'received' no existe antes de agregarla
            if (!Schema::hasColumn('cfes', 'received')) {
                $table->boolean('received')->default(false)->after('balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cfes', function (Blueprint $table) {
            // Verificar si la columna 'received' existe antes de eliminarla
            if (Schema::hasColumn('cfes', 'received')) {
                $table->dropColumn('received');
            }
        });
    }
};
