<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserRoleSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(CompanySettingsSeeder::class);
        $this->call(EcommerceSettingsSeeder::class);
    }
}
