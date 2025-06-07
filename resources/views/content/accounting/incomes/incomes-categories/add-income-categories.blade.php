<!-- Modal Add New Income Category -->
<div class="modal fade" id="addIncomeCategoryModal" tabindex="-1" aria-labelledby="addIncomeCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addIncomeCategoryModalLabel">Agregar Nueva Categoría de Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewIncomeCategoryForm">
          <div class="mb-3">
            <label for="income_name" class="form-label">Nombre de la Categoría</label>
            <input type="text" class="form-control" id="income_name" name="income_name" required placeholder="Ingrese el nombre de la categoría">
          </div>
          <div class="mb-3">
            <label for="income_description" class="form-label">Descripción</label>
            <textarea class="form-control" id="income_description" name="income_description" placeholder="Ingrese una descripción (opcional)" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitIncomeCategoryBtn"
          data-route="{{ route('income-categories.store') }}">Guardar Categoría</button>
      </div>
    </div>
  </div>
</div>
