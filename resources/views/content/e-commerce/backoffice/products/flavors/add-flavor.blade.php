<div class="modal fade" id="addFlavorModal" tabindex="-1" aria-labelledby="addFlavorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addFlavorModalLabel">Agregar nuevo sabor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewFlavorForm" method="POST" action="{{ route('product-flavors.store-modal') }}">
          @csrf <!-- Importante para la seguridad de formularios Laravel -->
          <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Estado</label>
            <select name="status" id="status" class="form-control">
              <option value="active" selected>Activo</option>
              <option value="inactive">Inactivo</option>
            </select>
          </div>
          <!-- Materias Primas -->
          <div class="card mb-4 addRawMaterials">
            <div class="card-header">
              <h5 class="card-title mb-0">Receta</h5>
            </div>
            <div class="card-body">
              <div data-repeater-list="recipes">
                <div data-repeater-item class="row mb-3">
                  <div class="col-4">
                    <label class="form-label" for="raw-material">Materia Prima</label>
                    <select class="form-select raw-material-select" name="recipes[0][raw_material_id]">
                      <option value="" disabled selected>Selecciona materia prima</option>
                      @foreach ($rawMaterials as $rawMaterial)
                        <option value="{{ $rawMaterial->id }}" data-unit="{{ $rawMaterial->unit_of_measure }}">{{ $rawMaterial->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-3">
                    <label class="form-label" for="quantity">Cantidad</label>
                    <input type="number" class="form-control" name="recipes[0][quantity]" placeholder="Cantidad" aria-label="Cantidad">
                  </div>
                  <div class="col-3 d-flex align-items-end">
                    <input type="text" class="form-control unit-of-measure" placeholder="Unidad de medida" readonly>
                  </div>
                  <div class="col-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger" data-repeater-delete>Eliminar</button>
                  </div>
                </div>
                <p class="text-muted">Cantidad para elaborar 1 (un) balde de {{ config('services.flavorUnit.unit') }} litros</p>
              </div>
              <button type="button" class="btn btn-primary" data-repeater-create>Agregar Ingrediente</button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('addNewFlavorForm').submit();">Guardar Sabor</button>
      </div>
    </div>
  </div>
</div>
