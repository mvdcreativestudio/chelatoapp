<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('company_settings')->insert([
            [
                'name' => 'Sumeria',
                'address' => '1234 Main Street',
                'city' => 'Montevideo',
                'state' => 'Montevideo',
                'country' => 'Uruguay',
                'phone' => '123-456-7890',
                'email' => 'info@sumeria.com.uy',
                'website' => 'https://www.sumeria.com.uy',
                'facebook' => 'https://www.facebook.com/mvdstudio',
                'twitter' => 'https://www.twitter.com/mvdstudio',
                'instagram' => 'https://www.instagram.com/mvdstudio',
                'linkedin' => 'https://www.linkedin.com/company/mvdstudio',
                'youtube' => 'https://www.youtube.com/mvdstudio',
                'logo_white' => 'assets/img/branding/logo-white.png',
                'logo_black' => 'assets/img/branding/logo-black.png',
                'rut' => '1234567890',
                'allow_registration' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }

    /**
     * Reverse the database seeds.
     *
     * @return void
     */
    public function down()
    {
        DB::table('company_settings')->truncate();
    }
}
