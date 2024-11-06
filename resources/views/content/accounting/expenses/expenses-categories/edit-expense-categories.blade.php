<!-- Modal Edit Expense Category -->
<div class="modal fade" id="editExpenseCategoryModal" tabindex="-1" aria-labelledby="editExpenseCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editExpenseCategoryModalLabel">Editar Categoría de Gasto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editExpenseCategoryForm">
          <div class="mb-3">
            <label for="nameEdit" class="form-label">Nombre de la Categoría</label>
            <input type="text" class="form-control" id="nameEdit" name="name" required>
          </div>
          <div class="mb-3">
            <label for="descriptionEdit" class="form-label">Descripción</label>
            <textarea class="form-control" id="descriptionEdit" name="description" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateExpenseCategoryBtn">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
