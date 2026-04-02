@extends('layouts.layoutMaster')

@section('title', "Permisos - {$role->name}")

@section('content')
<div class="d-flex justify-content-between align-items-center py-3 mb-4">
  <div>
    <h4 class="mb-1"><span class="text-muted fw-light">Roles /</span> Gestionar Permisos</h4>
    <p class="text-muted mb-0">Rol: <span class="fw-semibold text-body">{{ $role->name }}</span></p>
  </div>
  <div>
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary me-2">
      <i class="bx bx-arrow-back me-1"></i> Volver
    </a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<form action="{{ route('roles.assignPermissions', $role) }}" method="POST" id="permissionsForm">
  @csrf

  @php
    $moduleIcons = [
      'general' => 'bx-home-circle',
      'manufacturing' => 'bx-cog',
      'stock' => 'bx-package',
      'accounting' => 'bx-calculator',
      'datacenter' => 'bx-bar-chart-alt-2',
      'ecommerce' => 'bx-cart',
      'management' => 'bx-wrench',
      'marketing' => 'bx-megaphone',
      'crm' => 'bx-group',
      'point-of-sale' => 'bx-store',
      'orders' => 'bx-receipt',
      'expenses' => 'bx-wallet',
      'entries' => 'bx-book-open',
      'current-accounts' => 'bx-credit-card',
      'incomes' => 'bx-dollar-circle',
    ];
  @endphp

  <div class="row">
    @foreach($permissions->groupBy('module') as $module => $modulePermissions)
      @php
        $icon = $moduleIcons[$module] ?? 'bx-lock';
        $moduleName = __('modules.' . $module);
        if ($moduleName === 'modules.' . $module) {
            $moduleName = ucfirst(str_replace(['-', '_'], ' ', $module));
        }

        // Separar permisos por tipo
        $accessPerms = $modulePermissions->filter(fn($p) => str_starts_with($p->name, 'access_') && !str_starts_with($p->name, 'access_delete_'));
        $viewAllPerms = $modulePermissions->filter(fn($p) => str_starts_with($p->name, 'view_all_'));
        $deletePerms = $modulePermissions->filter(fn($p) => str_starts_with($p->name, 'access_delete_'));

        $totalPerms = $modulePermissions->count();
        $checkedPerms = $modulePermissions->filter(fn($p) => $role->hasPermissionTo($p->name))->count();
      @endphp

      <div class="col-12 mb-4">
        <div class="card shadow-none border">
          <div class="card-header d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx {{ $icon }}"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-0">{{ $moduleName }}</h6>
                <small class="text-muted">{{ $checkedPerms }} de {{ $totalPerms }} permisos activos</small>
              </div>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input module-toggle" type="checkbox" data-module="{{ $module }}" id="toggle_{{ $module }}" {{ $checkedPerms === $totalPerms ? 'checked' : '' }}>
              <label class="form-check-label small" for="toggle_{{ $module }}">Todos</label>
            </div>
          </div>

          <div class="card-body pt-2">
            {{-- Permisos de acceso --}}
            @if($accessPerms->isNotEmpty())
              <div class="mb-3">
                <small class="text-uppercase fw-semibold text-primary d-block mb-2">
                  <i class="bx bx-log-in-circle me-1"></i> Acceso
                </small>
                <div class="row g-2">
                  @foreach($accessPerms as $permission)
                    @php
                      $label = __('permissions.' . $permission->name);
                      if ($label === 'permissions.' . $permission->name) {
                          $label = ucfirst(str_replace(['access_', '-', '_'], ['', ' ', ' '], $permission->name));
                      }
                    @endphp
                    <div class="col-6 col-md-4 col-lg-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input perm-check module-{{ $module }}" type="checkbox" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" name="permissions[]" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $label }}</label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Permisos de visibilidad global --}}
            @if($viewAllPerms->isNotEmpty())
              <div class="mb-3">
                <small class="text-uppercase fw-semibold text-info d-block mb-2">
                  <i class="bx bx-globe me-1"></i> Visibilidad global
                </small>
                <div class="row g-2">
                  @foreach($viewAllPerms as $permission)
                    @php
                      $label = __('permissions.' . $permission->name);
                      if ($label === 'permissions.' . $permission->name) {
                          $label = 'Ver todos: ' . ucfirst(str_replace(['view_all_', '-', '_'], ['', ' ', ' '], $permission->name));
                      }
                    @endphp
                    <div class="col-6 col-md-4 col-lg-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input perm-check module-{{ $module }}" type="checkbox" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" name="permissions[]" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $label }}</label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Permisos de eliminación --}}
            @if($deletePerms->isNotEmpty())
              <div class="mb-0">
                <small class="text-uppercase fw-semibold text-danger d-block mb-2">
                  <i class="bx bx-trash me-1"></i> Eliminación
                </small>
                <div class="row g-2">
                  @foreach($deletePerms as $permission)
                    @php
                      $label = __('permissions.' . $permission->name);
                      if ($label === 'permissions.' . $permission->name) {
                          $label = 'Eliminar: ' . ucfirst(str_replace(['access_delete_', '-', '_'], ['', ' ', ' '], $permission->name));
                      }
                    @endphp
                    <div class="col-6 col-md-4 col-lg-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input perm-check module-{{ $module }}" type="checkbox" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" name="permissions[]" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $label }}</label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Botón fijo al pie --}}
  <div class="position-sticky bottom-0 py-3 bg-body" style="z-index: 10;">
    <div class="d-flex justify-content-end">
      <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
      <button type="submit" class="btn btn-primary">
        <i class="bx bx-save me-1"></i> Guardar Permisos
      </button>
    </div>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Toggle todos los permisos de un módulo
    document.querySelectorAll('.module-toggle').forEach(function (toggle) {
      toggle.addEventListener('change', function () {
        var module = this.dataset.module;
        var checked = this.checked;
        document.querySelectorAll('.module-' + module).forEach(function (cb) {
          cb.checked = checked;
        });
        updateModuleCount(module);
      });
    });

    // Actualizar toggle del módulo cuando cambian permisos individuales
    document.querySelectorAll('.perm-check').forEach(function (cb) {
      cb.addEventListener('change', function () {
        var moduleClass = Array.from(this.classList).find(c => c.startsWith('module-'));
        if (moduleClass) {
          var module = moduleClass.replace('module-', '');
          updateModuleToggle(module);
          updateModuleCount(module);
        }
      });
    });

    function updateModuleToggle(module) {
      var checkboxes = document.querySelectorAll('.module-' + module);
      var toggle = document.getElementById('toggle_' + module);
      if (toggle) {
        var allChecked = Array.from(checkboxes).every(cb => cb.checked);
        toggle.checked = allChecked;
      }
    }

    function updateModuleCount(module) {
      var checkboxes = document.querySelectorAll('.module-' + module);
      var total = checkboxes.length;
      var checked = Array.from(checkboxes).filter(cb => cb.checked).length;
      var toggle = document.getElementById('toggle_' + module);
      if (toggle) {
        var countEl = toggle.closest('.card-header').querySelector('small');
        if (countEl) {
          countEl.textContent = checked + ' de ' + total + ' permisos activos';
        }
      }
    }
  });
</script>
@endsection
