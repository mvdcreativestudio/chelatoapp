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
        @foreach($permissions as $permission)
          <div class="form-check card p-3 my-3">
            <div class="px-4">
              <input class="form-check-input" type="checkbox" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" name="permissions[]" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
              <label class="form-check-label" for="perm_{{ $permission->id }}">
                {{ __('permissions.' . $permission->name) }}
              </label>
            </div>
          </div>
        @endforeach
      </div>

      <button type="submit" class="btn btn-primary">Actualizar Permisos</button>
    </form>
  </div>
</div>
@endsection
