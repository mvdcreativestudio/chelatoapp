<!-- Modal Edit Entry Type -->
<div class="modal fade" id="editEntryTypeModal" tabindex="-1" aria-labelledby="editEntryTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editEntryTypeModalLabel">Editar Tipo de Asiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editEntryTypeForm">
          <div class="mb-3">
            <label for="edit_name" class="form-label">Nombre del Tipo de Asiento</label>
            <input type="text" class="form-control" id="edit_name" name="name" required placeholder="Ingrese el nombre del tipo de asiento">
          </div>
          <div class="mb-3">
            <label for="edit_description" class="form-label">Descripción</label>
            <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Ingrese una descripción (opcional)"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateEntryTypeBtn" data-route="{{ route('entry-types.update', ['entry_type' => ':id']) }}">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
