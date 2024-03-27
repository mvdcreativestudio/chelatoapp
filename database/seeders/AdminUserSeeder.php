<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = 'admin@mvdstudio.com.uy';
        $adminUser = User::where('email', $adminEmail)->first();

        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin MVDStudio',
                'email' => $adminEmail,
                'password' => Hash::make('4Dm1NMVD2024'),
            ]);
        }

        $adminRole = Role::where('name', 'Administrador')->first();

        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'Administrador', 'guard_name' => 'web']);
        }

        $adminUser->assignRole($adminRole);
    }
}
