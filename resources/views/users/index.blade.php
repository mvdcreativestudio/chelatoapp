<!-- Blade View -->

@extends('layouts/layoutMaster')

@section('title', 'Usuarios')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/cleavejs/cleave-phone.js'
])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
@vite('resources/assets/js/app-user-list.js')
@endsection

@section('content')

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
  <div class="alert alert-danger">
    {{ $error }}
  </div>
@endforeach
@endif

<div class="row g-4 mb-4">
  <!-- Summary cards omitted for brevity -->
</div>

<!-- Users List Table -->
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title">Usuarios</h5>
    <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
      <div class="col-md-4 user_role"></div>
      <div class="col-md-4 user_plan"></div>
      <div class="col-md-4 user_status"></div>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Rol</th>
          <th>Empresa</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas to add user -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Agregar Usuario</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
      <form id="addNewUserForm" action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="add-user-name">Nombre</label>
            <input type="text" class="form-control" id="add-user-name" name="name" placeholder="Nombre y Apellido" required />
        </div>
        <div class="mb-3">
            <label class="form-label" for="add-user-email">Email</label>
            <input type="email" id="add-user-email" class="form-control" name="email" placeholder="nombre@empresa.com" required />
        </div>
        <div class="mb-3">
            <label class="form-label" for="add-user-password">Contraseña</label>
            <input type="password" id="add-user-password" class="form-control" name="password" placeholder="••••••••" required />
        </div>
        <div class="mb-3">
            <label class="form-label" for="add-user-password-confirmation">Confirmar Contraseña</label>
            <input type="password" id="add-user-password-confirmation" class="form-control" name="password_confirmation" placeholder="••••••••" required />
        </div>
        <div class="mb-3">
            <label class="form-label" for="add-user-role">Rol</label>
            <select id="add-user-role" class="form-control select2" name="role" required>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="add-user-store">Empresa</label>
            <select id="add-user-store" class="form-control select2" name="store_id" required>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary me-sm-3 me-1">Crear Usuario</button>
        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>

  <!-- Offcanvas to edit user -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditUser" aria-labelledby="offcanvasEditUserLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasEditUserLabel" class="offcanvas-title">Editar Usuario</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0">
      <form class="edit-user pt-0" id="editUserForm" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label class="form-label" for="edit-user-name">Nombre</label>
          <input type="text" class="form-control" id="edit-user-name" name="name" placeholder="Nombre y Apellido" required />
        </div>
        <div class="mb-3">
          <label class="form-label" for="edit-user-email">Email</label>
          <input type="email" id="edit-user-email" class="form-control" name="email" placeholder="nombre@empresa.com" required />
        </div>
        <div class="mb-3">
          <label class="form-label" for="edit-user-password">Contraseña</label>
          <input type="password" id="edit-user-password" class="form-control" name="password" placeholder="••••••••" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="edit-user-password-confirmation">Confirmar Contraseña</label>
          <input type="password" id="edit-user-password-confirmation" class="form-control" name="password_confirmation" placeholder="••••••••" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="edit-user-role">Rol</label>
          <select id="edit-user-role" class="form-control select2" name="role" required>
            @foreach($roles as $role)
              <option value="{{ $role->name }}">{{ $role->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" for="edit-user-store">Empresa</label>
          <select id="edit-user-store" class="form-control select2" name="store_id" required>
            @foreach($stores as $store)
              <option value="{{ $store->id }}">{{ $store->name }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar Cambios</button>
        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>

@endsection
