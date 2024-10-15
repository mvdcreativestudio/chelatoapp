<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
  /**
   * Obtiene todos los usuarios.
   *
   * @return Collection
  */
  public function getAllUsers(): Collection
  {
    return User::all();
  }

  /**
   * Obtiene un usuario por su ID.
   *
   * @param int $id
   * @return User|null
  */
  public function getUserById(int $id): ?User
  {
    return User::with('roles')->find($id);
  }

  /**
   * Crea un nuevo usuario.
   *
   * @param array $data
   * @return User
  */
  public function createUser(array $data): User
  {
    $data['password'] = Hash::make($data['password']);
    return User::create($data);
  }

  /**
   * Actualiza un usuario existente.
   *
   * @param int $id
   * @param array $data
   * @return bool
  */
  public function updateUser(int $id, array $data, ?string $role = null): bool
  {
      $user = $this->getUserById($id);

      // Solo hashear la contraseña si está presente en los datos
      if (!empty($data['password'])) {
          $data['password'] = Hash::make($data['password']);
      } else {
          // Si no hay contraseña en los datos, no actualizar el campo 'password'
          unset($data['password']);
      }

      $updated = $user ? $user->update($data) : false;

      if ($updated && $role) {
          $user->syncRoles($role);
      }

      return $updated;
  }


  /**
   * Elimina un usuario por su ID.
   *
   * @param int $id
   * @return bool
  */
  public function deleteUser(int $id): bool
  {
    $user = $this->getUserById($id);
    return $user ? $user->delete() : false;
  }

  /**
   * Elimina los usuarios seleccionados.
   *
   * @param array $ids
   * @return bool
  */
  public function deleteSelectedUsers(array $ids): bool
  {
    return User::whereIn('id', $ids)->delete();
  }

  /**
   * Obtiene los datos para mostrar en la tabla de usuarios.
   *
   * @return \Illuminate\Support\Collection
   */
  public function datatable(): \Illuminate\Support\Collection
  {
    return User::with(['roles', 'store'])->select(['id', 'name', 'email', 'store_id'])->get()->map(function ($user) {
      return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'store_name' => $user->store->name ?? 'N/A',
        'roles' => $user->roles->pluck('name')->toArray()
      ];
    });
  }



}
