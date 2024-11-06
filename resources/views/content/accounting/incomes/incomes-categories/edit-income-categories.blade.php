<!-- Modal Edit Income Category -->
<div class="modal fade" id="editIncomeCategoryModal" tabindex="-1" aria-labelledby="editIncomeCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editIncomeCategoryModalLabel">Editar Categoría de Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editIncomeCategoryForm">
          <div class="mb-3">
            <label for="income_name_edit" class="form-label">Nombre de la Categoría</label>
            <input type="text" class="form-control" id="income_name_edit" name="income_name" required placeholder="Ingrese el nombre de la categoría">
          </div>
          <div class="mb-3">
            <label for="income_description_edit" class="form-label">Descripción</label>
            <textarea class="form-control" id="income_description_edit" name="income_description" placeholder="Ingrese una descripción (opcional)" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateIncomeCategoryBtn">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
