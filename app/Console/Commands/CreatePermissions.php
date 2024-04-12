<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class CreatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:modules-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea permisos basados en los módulos del CRM - MVD';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modulesJson = [
            'menu' => [
                [
                    'slug' => 'dashboard',
                    'view_all' => false,
                ],
                [
                    'slug' => 'raw-materials',
                    'view_all' => true,
                ],
                [
                    'slug' => 'suppliers',
                    'view_all' => true,
                ],
                [
                    'slug' => 'supplier-orders',
                    'view_all' => true,
                ],
                [
                    'slug' => 'stock',
                    'view_all' => false,
                ],
                [
                    'slug' => 'accounting',
                    'view_all' => false,
                ],
                [
                    'slug' => 'ecommerce',
                    'view_all' => false,
                ],
                [
                    'slug' => 'omnichannel',
                    'view_all' => false,
                ],
                [
                    'slug' => 'datacenter',
                    'view_all' => false,
                ],
                [
                    'slug' => 'crm',
                    'view_all' => false,
                ],
                [
                    'slug' => 'stores',
                    'view_all' => false,
                ],
                [
                    'slug' => 'roles',
                    'view_all' => false,
                ]
            ]
        ];

         // Asegurar que el rol de administrador existe
         $adminRole = Role::firstOrCreate(['name' => 'Administrador']);

         foreach ($modulesJson['menu'] as $module) {
             $permissionName = 'access_' . $module['slug'];
             $viewAllPermissionName = $module['view_all'] ? 'view_all_' . $module['slug'] : null;

             // Crear o obtener el permiso
             $permission = Permission::firstOrCreate(['name' => $permissionName]);
             $this->info('Permiso asegurado: ' . $permissionName);

             // Asignar el permiso al rol de administrador
             $adminRole->givePermissionTo($permission);

             if ($viewAllPermissionName) {
                 $viewAllPermission = Permission::firstOrCreate(['name' => $viewAllPermissionName]);
                 $this->info('Permiso de visualización total asegurado: ' . $viewAllPermissionName);
                 // Asignar el permiso de visualización total al rol de administrador
                 $adminRole->givePermissionTo($viewAllPermission);
             }
         }

         $this->info('Todos los permisos han sido creados y asignados al rol de administrador.');
    }
}
