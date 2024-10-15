<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EcommerceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('ecommerce_settings')->insert([
          'enable_coupons'      => true,
          'notifications_email' => 'admin@mvdstudio.com.uy',
          'currency'            => 'UYU',
          'currency_symbol'     => '$',
          'decimal_separator'   => ',',
          'thousands_separator' => '.'
      ]);
    }

    /**
     * Reverse the database seeds.
     *
     * @return void
    */
    public function down()
    {
      DB::table('ecommerce_settings')->truncate();
    }
}
