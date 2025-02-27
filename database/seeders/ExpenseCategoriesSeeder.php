<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('expense_categories')->insert([
            ['name' => 'Servicios Públicos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Compra de Insumos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alquiler', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nómina (RRHH)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gastos Administrativos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Publicidad / Marketing', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transporte y Logística', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mantenimiento y Reparaciones', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maquinaria', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mobiliario', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Otros', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the database seeds.
     *
     * @return void
     */
    public function down()
    {
        DB::table('expense_categories')->truncate();
    }
}
