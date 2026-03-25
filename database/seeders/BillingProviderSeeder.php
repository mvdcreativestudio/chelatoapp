<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingProviderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('billing_providers')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'PyMo',
                'code' => 'pymo',
                'base_url' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'SICFE',
                'code' => 'sicfe',
                'base_url' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
