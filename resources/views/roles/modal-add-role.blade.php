<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Agregar Nuevo Rol</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="newRoleName" class="form-label">Nombre del Rol</label>
            <input type="text" class="form-control" id="newRoleName" name="name" required placeholder="Nombre del rol">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Crear Rol</button>
        </div>
      </form>
    </div>
  </div>
</div>
