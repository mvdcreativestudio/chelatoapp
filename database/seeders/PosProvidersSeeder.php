<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Si ya hay proveedores de POS, no se crean más
        if (DB::table('pos_providers')->count() > 0) {
            return;
        }

        DB::table('pos_providers')->insert([
            ['name' => 'Scanntech', 'requires_token' => '1', 'api_url' => 'http://200.40.123.21:3500/rest/v2/'],
            ['name' => 'Fiserv', 'requires_token' => '0', 'api_url' => 'https://testitd.firstdata.com/v2/ITDService/'],
            ['name' => 'Handy', 'requires_token' => '0', 'api_url' => 'https://poslink.hm.opos.com.uy/itdServer/'],
            ['name' => 'OCA', 'requires_token' => '0', 'api_url' => 'https://poslink.hm.opos.com.uy/itdServer/'],

        ]);
    }

    /**
     * Reverse the database seeds.
     *
     * @return void
     */
    public function down()
    {
        DB::table('pos_providers')->truncate();
    }
}
