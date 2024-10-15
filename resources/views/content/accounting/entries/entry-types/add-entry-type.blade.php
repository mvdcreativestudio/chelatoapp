<!-- Modal Add New Entry Type -->
<div class="modal fade" id="addEntryTypeModal" tabindex="-1" aria-labelledby="addEntryTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addEntryTypeModalLabel">Agregar Nuevo Tipo de Asiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewEntryTypeForm">
          <div class="mb-3">
            <label for="name" class="form-label">Nombre del Tipo de Asiento</label>
            <input type="text" class="form-control" id="name" name="name" required placeholder="Ingrese el nombre del tipo de asiento">
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Ingrese una descripción (opcional)"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitEntryTypeBtn" data-route="{{ route('entry-types.store') }}">Guardar Tipo de Asiento</button>
      </div>
    </div>
  </div>
</div>
