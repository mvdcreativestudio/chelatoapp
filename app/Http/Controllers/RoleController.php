<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * El repositorio para las operaciones de roles.
     *
     * @var StoreRepository
     */
    protected RoleRepository $roleRepo;

    /**
     * Inyecta el repositorio en el controlador.
     *
     * @param  StoreRepository  $storeRepository
    */
    public function __construct(RoleRepository $roleRepo)
    {
        $this->middleware('is_admin')->only(
          [
            'store',
            'update',
            'manageUsers',
            'associateUser',
            'disassociateUser'
          ]
        );

        $this->middleware('check_permission:access_roles')->only(
          [
            'index',
            'managePermissions',
            'assignPermissions',
            'store',
            'update',
            'manageUsers',
            'associateUser',
            'disassociateUser'
          ]
        );

        $this->roleRepo = $roleRepo;
    }

    /**
     * Muestra la lista de roles.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $roles = $this->roleRepo->getAllRoles();
        return view('roles.index', compact('roles'));
    }

    /**
     * Crea un nuevo rol.
     *
     * @param StoreRoleRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRoleRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->roleRepo->createRole($request->name);
        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
    }

    /**
     * Actualiza un rol existente.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRoleRequest $request, Role $role): \Illuminate\Http\RedirectResponse
    {
        if ($role->name === 'Administrador') {
            return redirect()->route('roles.index')->with('error', 'No se puede actualizar el rol de administrador.');
        }

        $this->roleRepo->updateRole($role, $request->name);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Muestra la vista para gestionar usuarios asociados a un rol.
     *
     * @param Role $role
     * @return \Illuminate\Contracts\View\View
     */
    public function manageUsers(Role $role): \Illuminate\Contracts\View\View
    {
        $unassociatedUsers = $this->roleRepo->getUnassociatedUsers($role);
        $associatedUsers = $this->roleRepo->getAssociatedUsers($role);
        return view('roles.manage-users', compact('role', 'unassociatedUsers', 'associatedUsers'));
    }

    /**
     * Asocia un usuario a un rol.
     *
     * @param Role $role
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function associateUser(Role $role, Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->roleRepo->associateUserToRole($role, $request->user_id);
        return redirect()->route('roles.manageUsers', $role)->with('success', 'Usuario asociado correctamente.');
    }

    /**
     * Desasocia un usuario de un rol.
     *
     * @param Role $role
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disassociateUser(Role $role, Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->roleRepo->disassociateUserFromRole($role, $request->user_id);
        return redirect()->route('roles.manageUsers', $role)->with('success', 'Usuario desasociado correctamente.');
    }

    /**
     * Muestra la vista para gestionar permisos asociados a un rol.
     *
     * @param Role $role
     * @return \Illuminate\Contracts\View\View
     */
    public function managePermissions(Role $role): \Illuminate\Contracts\View\View
    {
        $permissions = $this->roleRepo->getAllPermissions();
        return view('roles.manage-permissions', compact('role', 'permissions'));
    }

    /**
     * Asigna permisos a un rol específico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\Permission\Models\Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignPermissions(Request $request, Role $role)
    {
        $this->roleRepo->assignPermissionsToRole($role, $request->permissions);
        return redirect()->route('roles.index')->with('success', 'Permisos actualizados correctamente.');
    }
}
