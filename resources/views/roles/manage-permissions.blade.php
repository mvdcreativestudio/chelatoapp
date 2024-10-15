@extends('layouts.layoutMaster')

@section('title', "Gestionar Permisos del Rol - {$role->name}")

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Roles /</span> Gestionar Permisos
</h4>

<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title">Permisos del rol {{ $role->name }}:</h5>
    <form action="{{ route('roles.assignPermissions', $role) }}" method="POST">
      @csrf
      <div class="mb-3">
        @foreach($permissions->groupBy('module') as $module => $modulePermissions)
          <div class="card my-3">
            <div class="card-header">
              <h6>{{ __('modules.' . $module) }}</h6>
            </div>
            <div class="card-body">
              @foreach($modulePermissions as $permission)
              <div class="col-6 col-md-4 col-lg-3 mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" name="permissions[]" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                  <label class="form-check-label" for="perm_{{ $permission->id }}">
                    {{ __('permissions.' . $permission->name) }}
                  </label>
                </div>
              </div>
            @endforeach
            </div>
          </div>
        @endforeach
      </div>

      <button type="submit" class="btn btn-primary">Actualizar Permisos</button>
    </form>
  </div>
</div>

<style>
  .form-check-label {
    font-size: 0.875rem;
  }
  .form-switch .form-check-input {
    width: 2rem;
    height: 1rem;
    margin-left: -2.5rem;
  }
  .form-switch .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
  }
</style>

@endsection
