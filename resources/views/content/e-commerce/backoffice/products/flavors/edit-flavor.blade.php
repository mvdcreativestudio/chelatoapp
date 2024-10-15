<!-- Modal de Edición de Sabor -->
<div class="modal fade" id="editFlavorModal" tabindex="-1" aria-labelledby="editFlavorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editFlavorModalLabel">Editar Sabor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editFlavorForm">
          <div class="mb-3">
            <label for="flavorName" class="form-label">Nombre del Sabor</label>
            <input type="text" class="form-control" id="flavorName" name="flavorName" required>
          </div>
          <!-- Materias Primas -->
          <div class="card mb-4 addRawMaterials">
            <div class="card-header">
              <h5 class="card-title mb-0">Receta</h5>
            </div>
            <div class="card-body">
              <div data-repeater-list="recipes" id="editRecipesList">
                <!-- Items se añadirán aquí dinámicamente -->
              </div>
              <button type="button" class="btn btn-primary" data-repeater-create>Agregar Ingrediente</button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateFlavorBtn">Actualizar Sabor</button>
      </div>
    </div>
  </div>
</div>
