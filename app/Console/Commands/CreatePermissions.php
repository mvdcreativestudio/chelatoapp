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
    protected $description = 'Crea permisos basados en los módulos del CRM - Sumeria';

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
                    'module' => 'general',
                    'view_all' => false,
                ],
                [
                    'slug' => 'manufacturing',
                    'module' => 'manufacturing',
                    'view_all' => false,
                ],
                [
                    'slug' => 'raw-materials',
                    'module' => 'stock',
                    'view_all' => true,
                ],
                [
                    'slug' => 'raw-materials-edit',
                    'module' => 'manufacturing',
                    'view_all' => false,
                ],
                [
                    'slug' => 'formulas',
                    'module' => 'manufacturing',
                    'view_all' => true,
                ],
                [
                    'slug' => 'batches',
                    'module' => 'manufacturing',
                    'view_all' => true,
                ],
                [
                    'slug' => 'packagings',
                    'module' => 'manufacturing',
                    'view_all' => false,
                ],
                [
                    'slug' => 'suppliers',
                    'module' => 'stock',
                    'view_all' => true,
                ],
                [
                    'slug' => 'purchase-orders',
                    'module' => 'stock',
                    'view_all' => true,
                ],
                [
                    'slug' => 'stock',
                    'module' => 'stock',
                    'view_all' => false,
                ],
                [
                    'slug' => 'accounting',
                    'module' => 'accounting',
                    'submenus' => [
                        'invoices',
                        'receipts',
                        'entries',
                        'accounting-settings'
                    ],
                    'view_all' => false,
                ],
                [
                    'slug' => 'clients',
                    'module' => 'crm',
                    'view_all' => true,
                ],
                [
                    'slug' => 'client-sensitive-data',
                    'module' => 'crm',
                    'view_all' => false,
                ],
                [
                    'slug' => 'price-lists',
                    'module' => 'sales',
                    'submenus' => [
                        'create-price-lists',
                        'show-price-lists',
                        'edit-price-lists',
                        'delete-price-lists'
                    ],
                    'view_all' => true,
                ],
                [
                    'slug' => 'ecommerce',
                    'module' => 'ecommerce',
                    'submenus' => [
                        'orders',
                        'products',
                        'settings',
                        'product-flavors',
                        'product-categories',
                        'composite-products',
                        'bulk-products',
                    ],
                    'view_all' => true,
                ],
                [
                    'slug' => 'global_products',
                    'module' => 'ecommerce',
                    'view_all' => false,
                ],
                [
                    'slug' => 'bulk-productions',
                    'module' => 'manufacturing',
                    'view_all' => true,
                ],
                [
                    'slug' => 'bypass_raw_material_check',
                    'module' => 'manufacturing',
                    'view_all' => false,
                ],
                [
                    'slug' => 'marketing',
                    'module' => 'marketing',
                    'submenus' => [
                        'coupons',
                        'settings',
                    ],
                    'view_all' => false,
                ],
                [
                    'slug' => 'omnichannel',
                    'module' => 'marketing',
                    'submenus' => [
                        'chats',
                        'settings',
                    ],
                    'view_all' => false,
                ],
                [
                    'slug' => 'datacenter',
                    'module' => 'datacenter',
                    'view_all' => true,
                ],
                [
                    'slug' => 'stores',
                    'module' => 'management',
                    'view_all' => true,
                ],
                [
                    'slug' => 'roles',
                    'module' => 'management',
                    'view_all' => false,
                ],
                [
                    'slug' => 'company_settings',
                    'module' => 'management',
                    'view_all' => false,
                ],
                [
                    'slug' => 'open_close_stores',
                    'module' => 'management',
                    'view_all' => false,
                ],
                [
                    'slug' => 'point-of-sale',
                    'module' => 'point-of-sale',
                    'view_all' => false,
                ],
                [
                    'slug' => 'sales-commerce',
                    'module' => 'ecommerce',
                    'view_all' => false,
                ],
                [
                    'slug' => 'users',
                    'module' => 'management',
                    'view_all' => false,
                ],
                [
                    'slug' => 'user-accounts',
                    'module' => 'management',
                    'view_all' => false,
                ],
                [
                    'slug' => 'cash-registers',
                    'module' => 'point-of-sale',
                    'view_all' => true,
                ],
                [
                    'slug' => 'orders',
                    'module' => 'orders',
                    'view_all' => true,
                ],
                [
                    'slug' => 'expenses',
                    'module' => 'expenses',
                    'view_all' => true,
                    "submenus" => [
                        "delete_expenses",
                        "expense-categories",
                    ],
                ],
                [
                    "slug" => "expense-categories",
                    "module" => "expenses",
                    "view_all" => true,
                    "submenus" => [
                        "delete_expense-categories",
                    ],
                ],
                [
                    'slug' => 'entries',
                    'module' => 'entries',
                    'view_all' => true,
                    'submenus' => [
                        'delete_entries',
                        'entry-details',
                        'entry-types',
                        'entry-accounts',
                    ],
                ],
                [
                    'slug' => 'entry-details',
                    'module' => 'accounting',
                    'view_all' => false,
                ],
                [
                    'slug' => 'entry-types',
                    'module' => 'accounting',
                    'view_all' => true,
                    'submenus' => [
                        'delete_entry-types',
                    ],
                ],
                [
                    'slug' => 'entry-accounts',
                    'module' => 'accounting',
                    'view_all' => true,
                    'submenus' => [
                        'delete_entry-accounts',
                    ],
                ],
                [
                    'slug' => 'composite-products',
                    'module' => 'ecommerce',
                    'view_all' => true,
                    'submenus' => [
                        'delete_composite-products',
                    ],
                ],
                [
                    'slug' => 'current-accounts',
                    'module' => 'current-accounts',
                    'view_all' => true,
                    'submenus' => [
                        'current-accounts',
                        'current-account-settings',
                    ],
                ],
                [
                    'slug' => 'current-account-payments',
                    'module' => 'current-accounts',
                    'view_all' => true,
                    'submenus' => [
                        'delete_current-account-payments',
                    ],
                ],
                [
                    'slug' => 'current-account-settings',
                    'module' => 'current-accounts',
                    'view_all' => true,
                    'submenus' => [
                        'delete_current-account-settings',
                    ],
                ],
                [
                    'slug' => 'incomes',
                    'module' => 'incomes',
                    'view_all' => true,
                    'submenus' => [
                        'incomes',
                        'delete_incomes',
                        'income-categories',
                    ],
                ],
                [
                    'slug' => 'income-categories',
                    'module' => 'incomes',
                    'view_all' => true,
                    'submenus' => [
                        'delete_income-categories',
                    ],
                ],
                [
                    'slug' => 'currencies',
                    'module' => 'accounting',
                    'view_all' => true,
                    'submenus' => [
                        'delete_currencies',
                    ],
                ]
            ],
        ];

        // Asegurar que el rol de superadmin existe
        $superAdminRole = Role::firstOrCreate(['name' => 'Superadmin']);

        // Asegurar que el rol de administrador existe
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);

        foreach ($modulesJson['menu'] as $module) {
            $this->createPermission($module['slug'], $module['view_all'], $adminRole, $superAdminRole, $module['module']);

            if (array_key_exists('submenus', $module)) {
                foreach ($module['submenus'] as $submenuSlug) {
                    $this->createPermission($submenuSlug, false, $adminRole, $superAdminRole, $module['module']);
                }
            }
        }

        $this->info('Todos los permisos han sido creados y asignados al rol Administrador y Superadmin.');
    }

    private function createPermission($slug, $viewAll, $adminRole, $superAdminRole, $module)
    {
        // Crear o buscar el permiso base y asignar el módulo
        $permissionName = 'access_' . $slug;
        $permission = Permission::updateOrCreate(
            ['name' => $permissionName],
            ['module' => $module]
        );
        $adminRole->givePermissionTo($permission);
        $superAdminRole->givePermissionTo($permission);
        $this->info('Permiso creado y asignado a los roles Administrador y Superadmin: ' . $permissionName);

        // Si es necesario crear el permiso de vista total
        if ($viewAll) {
            $viewAllPermissionName = 'view_all_' . $slug;
            $viewAllPermission = Permission::firstOrCreate(
                ['name' => $viewAllPermissionName],
                ['module' => $module]
            );
            $adminRole->givePermissionTo($viewAllPermission);
            $superAdminRole->givePermissionTo($viewAllPermission);
            $this->info('Permiso de vista total creado y asignado a los roles Administrador y Superadmin: ' . $viewAllPermissionName);
        }
    }
}
