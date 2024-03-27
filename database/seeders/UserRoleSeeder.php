<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Crea el rol 'user'
        Role::create(['name' => 'Usuario', 'guard_name' => 'web']);
    }
}
