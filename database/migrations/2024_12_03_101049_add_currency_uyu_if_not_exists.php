<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si la moneda ya existe
        $existingCurrency = DB::table('currencies')->where('code', 'UYU')->first();

        if (!$existingCurrency) {
            DB::table('currencies')->insert([
                'code' => 'UYU',
                'symbol' => '$',
                'name' => 'Peso Uruguayo',
                'exchange_rate' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $existingCurrency = DB::table('currencies')->where('code', 'ARS')->first();
        if (!$existingCurrency) {
            DB::table('currencies')->insert([
                'code' => 'ARS',
                'symbol' => '$',
                'name' => 'Pesos Argentinos',
                'exchange_rate' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('currencies')->where('code', 'UYU')->delete();
    }
};
