<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\UserRepository;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
  /**
   * El repositorio de usuarios.
   *
   * @var UserRepository
  */
  protected UserRepository $userRepository;

  /**
   * Inyecta el repositorio en el controlador.
   *
   * @param UserRepository $userRepository
  */
  public function __construct(UserRepository $userRepository)
  {
    $this->middleware(['check_permission:access_users'])->only(
      [
        'index',
        'show',
        'store',
        'edit',
        'update',
        'destroy',
        'deleteSelected',
        'datatable',
        'create'
      ]
    );

    $this->userRepository = $userRepository;
  }

  /**
   * Muestra una lista de usuarios.
   *
   * @return View
  */
  public function index(): View
  {
    $users = $this->userRepository->getAllUsers();
    $roles = Role::all();
    $stores = Store::all();
    return view('users.index', compact('users', 'roles', 'stores'));
  }


  /**
   * Muestra los detalles de un usuario específico.
   *
   * @param int $id
   * @return JsonResponse
  */
  public function show(int $id): JsonResponse
  {
    $user = $this->userRepository->getUserById($id);
    return response()->json($user);
  }

/**
 * Almacena un nuevo usuario en la base de datos.
 *
 * @param StoreUserRequest $request
 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
 */
public function store(StoreUserRequest $request)
{
    try {
        $user = $this->userRepository->createUser($request->validated());
        $user->syncRoles($request->input('role'));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente.',
                'data' => $user
            ], 201); // Devuelve un código de estado 201 para creación exitosa
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    } catch (\Exception $e) {
        \Log::error('Error creating user: ' . $e->getMessage());

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear el usuario.'
            ], 500); // Devuelve un código de estado 500 para error del servidor
        }

        return redirect()->route('users.index')->with('error', 'No se pudo crear el usuario.');
    }
}

  /**
   * Muestra el formulario para editar un usuario existente.
   *
   * @param int $id
   * @return View
  */
  public function edit(int $id): View
  {
    $user = $this->userRepository->getUserById($id);
    $roles = Role::all();
    return view('users.edit', compact('user', 'roles'));
  }

  /**
   * Actualiza un usuario existente en la base de datos.
   *
   * @param UpdateUserRequest $request
   * @param int $id
   * @return JsonResponse
  */
  public function update(UpdateUserRequest $request, int $id): JsonResponse
  {
      $data = $request->validated();

      // Si no se proporciona una nueva contraseña, elimínala de los datos de la solicitud
      if (empty($data['password'])) {
          unset($data['password']);
      }

      try {
          $result = $this->userRepository->updateUser($id, $data, $request->input('role'));

          return response()->json([
              'success' => $result,
              'message' => $result ? 'Usuario actualizado con éxito.' : 'No se pudo actualizar el usuario.',
          ]);
      } catch (\Exception $e) {
          \Log::error('Error updating user: ' . $e->getMessage());
          return response()->json([
              'success' => false,
              'message' => 'No se pudo actualizar el usuario.'
          ], 500);
      }
  }




  /**
   * Elimina un usuario específico de la base de datos.
   *
   * @param int $id
   * @return JsonResponse
  */
  public function destroy(int $id): JsonResponse
  {
    $result = $this->userRepository->deleteUser($id);
    return response()->json($result);
  }

  /**
   * Elimina los usuarios seleccionados de la base de datos.
   *
   * @param Request $request
   * @return JsonResponse
  */
  public function deleteSelected(Request $request): JsonResponse
  {
    $result = $this->userRepository->deleteSelectedUsers($request->ids);
    return response()->json($result);
  }

  /**
   * Obtiene los datos para mostrar en la tabla de usuarios.
   *
   * @return JsonResponse
   */
  public function datatable(): JsonResponse
  {
      $users = $this->userRepository->datatable();
      return response()->json(['data' => $users]);
  }

}
