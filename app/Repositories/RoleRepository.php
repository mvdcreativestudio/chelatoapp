<?php

namespace App\Repositories;

use Spatie\Permission\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class RoleRepository
{
    /**
     * Obtiene todos los roles y el conteo de usuarios asociados a cada uno.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRoles(): \Illuminate\Database\Eloquent\Collection
    {
        $roles = Role::all();

        $roles->map(function ($role) {
            $role->users_count = $role->users->count();
            return $role;
        });

        return $roles;
    }

    /**
     * Crea un nuevo rol.
     *
     * @param string $name Nombre del rol.
     * @return \Spatie\Permission\Models\Role
     */
    public function createRole(string $name): Role
    {
        return Role::create(['guard_name' => 'web', 'name' => $name]);
    }

    /**
     * Actualiza el nombre de un rol existente.
     *
     * @param Role $role Instancia del rol a actualizar.
     * @param string $name Nuevo nombre para el rol.
     */
    public function updateRole(Role $role, string $name): void
    {
        $role->update(['name' => $name]);
    }

    /**
     * Obtiene usuarios no asociados a un rol específico.
     *
     * @param Role $role Rol para el cual buscar usuarios no asociados.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnassociatedUsers(Role $role): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereDoesntHave('roles', function ($query) use ($role) {
            $query->where('id', $role->id);
        })->get();
    }

    /**
     * Obtiene usuarios asociados a un rol específico.
     *
     * @param Role $role Rol para el cual buscar usuarios asociados.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssociatedUsers(Role $role): \Illuminate\Database\Eloquent\Collection
    {
        return $role->users;
    }

    /**
     * Asocia un usuario a un rol.
     *
     * @param Role $role Rol al cual asociar el usuario.
     * @param int $userId ID del usuario a asociar.
     */
    public function associateUserToRole(Role $role, int $userId): void
    {
        $user = User::find($userId);
        $user->assignRole($role);
    }

    /**
     * Desasocia un usuario de un rol.
     *
     * @param Role $role Rol del cual desasociar el usuario.
     * @param int $userId ID del usuario a desasociar.
     */
    public function disassociateUserFromRole(Role $role, int $userId): void
    {
        $user = User::find($userId);
        $user->removeRole($role);
    }

    /**
     * Obtiene todos los permisos disponibles.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Permission::all();
    }

    /**
     * Asigna permisos a un rol específico.
     *
     * @param Role $role Rol al cual asignar los permisos.
     * @param array $permissions Permisos a asignar.
     */
    public function assignPermissionsToRole(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }
}
