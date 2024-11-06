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
        // Paso 1: Agregar 'CI' al conjunto ENUM sin eliminar 'DNI'
        Schema::table('suppliers', function (Blueprint $table) {
            DB::statement("ALTER TABLE suppliers MODIFY COLUMN doc_type ENUM('DNI', 'CI', 'PASSPORT', 'OTHER', 'RUT')");
        });

        // Paso 2: Actualizar los valores de 'DNI' a 'CI'
        DB::table('suppliers')
            ->where('doc_type', 'DNI')
            ->update(['doc_type' => 'CI']);
        
        // Paso 3: Eliminar 'DNI' del conjunto ENUM
        Schema::table('suppliers', function (Blueprint $table) {
            DB::statement("ALTER TABLE suppliers MODIFY COLUMN doc_type ENUM('CI', 'PASSPORT', 'OTHER', 'RUT')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el proceso: Primero, agregar 'DNI' y luego revertir los valores de 'CI' a 'DNI'
        
        // Paso 1: Agregar 'DNI' al conjunto ENUM
        Schema::table('suppliers', function (Blueprint $table) {
            DB::statement("ALTER TABLE suppliers MODIFY COLUMN doc_type ENUM('DNI', 'CI', 'PASSPORT', 'OTHER', 'RUT')");
        });

        // Paso 2: Actualizar los valores de 'CI' a 'DNI'
        DB::table('suppliers')
            ->where('doc_type', 'CI')
            ->update(['doc_type' => 'DNI']);
        
        // Paso 3: Eliminar 'CI' del conjunto ENUM
        Schema::table('suppliers', function (Blueprint $table) {
            DB::statement("ALTER TABLE suppliers MODIFY COLUMN doc_type ENUM('DNI', 'PASSPORT', 'OTHER', 'RUT')");
        });
    }
};
