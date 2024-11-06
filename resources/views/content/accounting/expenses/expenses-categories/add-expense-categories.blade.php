<!-- Modal Add New Expense Category -->
<div class="modal fade" id="addExpenseCategoryModal" tabindex="-1" aria-labelledby="addExpenseCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addExpenseCategoryModalLabel">Agregar Nueva Categoría de Gasto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewExpenseCategoryForm">
          <div class="mb-3">
            <label for="name" class="form-label">Nombre de la Categoría</label>
            <input type="text" class="form-control" id="name" name="name" required placeholder="Ingrese el nombre de la categoría">
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea class="form-control" id="description" name="description" placeholder="Ingrese una descripción (opcional)" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitExpenseCategoryBtn" 
          data-route="{{ route('expense-categories.store') }}">Guardar Categoría</button>
      </div>
    </div>
  </div>
</div>
