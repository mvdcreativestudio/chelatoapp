<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncomeCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('income_categories')->insert([
            ['income_name' => 'Servicios Públicos', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Compra de Insumos', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Alquiler', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Nómina (RRHH)', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Gastos Administrativos', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Publicidad / Marketing', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Transporte y Logística', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Mantenimiento y Reparaciones', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Maquinaria', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Mobiliario', 'created_at' => now(), 'updated_at' => now()],
            ['income_name' => 'Otros', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the database seeds.
     *
     * @return void
     */
    public function down()
    {
        DB::table('income_categories')->truncate();
    }
}
