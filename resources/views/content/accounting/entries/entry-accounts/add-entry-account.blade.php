<!-- Modal Add New Entry Account -->
<div class="modal fade" id="addEntryAccountModal" tabindex="-1" aria-labelledby="addEntryAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addEntryAccountModalLabel">Agregar Nueva Cuenta Contable</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewEntryAccountForm">
          <div class="mb-3">
            <label for="code" class="form-label">Código de la Cuenta Contable</label>
            <input type="number" class="form-control" id="code" name="code" required placeholder="Ingrese el código de la cuenta contable">
          </div>
          <div class="mb-3">
            <label for="name" class="form-label">Nombre de la Cuenta Contable</label>
            <input type="text" class="form-control" id="name" name="name" required placeholder="Ingrese el nombre de la cuenta contable">
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Ingrese una descripción (opcional)"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitEntryAccountBtn" data-route="{{ route('entry-accounts.store') }}">Guardar Cuenta Contable</button>
      </div>
    </div>
  </div>
</div>
