<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

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
    protected $description = 'Crea permisos basados en los módulos del CRM - MDV';

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

        foreach ($modulesJson['menu'] as $module) {
            $permissionName = 'access_' . $module['slug'];

            if (array_key_exists('view_all', $module) && $module['view_all']) {
                $viewAllPermissioNName = 'view_all_' . $module['slug'];
            }

            if (!Permission::where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName]);
                $this->info('Permiso creado: ' . $permissionName);
            } else {
                $this->info('El permiso ya existe: ' . $permissionName);
            }

            if (isset($viewAllPermissioNName) && !Permission::where('name', $viewAllPermissioNName)->exists()) {
                Permission::create(['name' => $viewAllPermissioNName]);
                $this->info('Permiso creado: ' . $viewAllPermissioNName);
            } elseif (isset($viewAllPermissioNName)) {
                $this->info('El permiso ya existe: ' . $viewAllPermissioNName);
            }
        }

        $this->info('Todos los permisos han sido creados.');
    }
}
