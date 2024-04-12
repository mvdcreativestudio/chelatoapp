{{-- roles.blade.php --}}
@extends('layouts/layoutMaster')

@section('title', 'Roles')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
])
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
])
@endsection

@section('page-script')
@vite('resources/assets/js/app-roles-list.js')
<script type="text/javascript">
  var roles = @json($roles);
  window.isAdmin = @json(auth()->user()->hasRole('Administrador'));
</script>
@endsection

@section('content')
<div class="d-flex justify-content-between mb-4">
  <h4><span class="text-muted fw-light">Administración /</span> Roles</h4>
</div>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
  {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
  <div class="alert alert-danger">
    {{ $error }}
  </div>
@endforeach
@endif

@if ($errors->any())
  @foreach ($errors->all() as $error)
    <div class="alert alert-danger mt-3 mb-3">
      {{ $error }}
    </div>
  @endforeach
@endif

<div class="card mb-4">
  <div class="card-body">
    <div class="row gy-4 gy-sm-1">
      <div class="col-sm-6 col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2">Roles</h6>
                        <h4>{{ isset($roles) ? $roles->count() : 0}}</h4>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-user bx-sm"></i></span>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title">Listado de Roles</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table id="rolesTable" class="table border-top">
      <thead>
        <tr>
          <th>Nombre del Rol</th>
          <th>Guard Name</th>
          <th>Miembros</th>
            @if (auth()->user()->hasRole('Administrador'))
          <th>Acciones</th>
          @endif
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>

@include('roles.modal-edit-role')
@include('roles.modal-add-role')

@endsection
