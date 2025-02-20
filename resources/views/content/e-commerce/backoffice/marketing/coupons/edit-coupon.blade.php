<!-- Modal de Edición de Cupón -->
<div class="modal fade" id="editCouponModal" tabindex="-1" aria-labelledby="editCouponModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editCouponModalLabel">Editar Cupón</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editCouponForm">
          <div class="mb-3">
            <label for="couponCode" class="form-label">Código</label>
            <input type="text" class="form-control" id="couponCode" name="couponCode" required>
          </div>
          <div class="mb-3">
            <label for="couponType" class="form-label">Tipo</label>
            <select class="form-select" id="couponType" name="couponType">
              <option value="fixed">Fijo</option>
              <option value="percentage">Porcentaje</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="couponAmount" class="form-label">Valor</label>
            <input type="number" class="form-control" id="couponAmount" name="couponAmount" required>
          </div>
          <div class="mb-3">
            <label for="couponInit" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="couponInit" name="couponInit">
          </div>
          <div class="mb-3">
            <label for="couponExpiry" class="form-label">Fecha de Expiración</label>
            <input type="date" class="form-control" id="couponExpiry" name="couponExpiry">
          </div>

          <!-- Selección de productos excluidos -->
          <div class="mb-3">
            <label class="form-label">Productos Excluidos</label>
            <div id="editExcludedProductsList">
              @foreach($products as $product)
                <div class="form-check">
                  <input type="checkbox" class="form-check-input editExcludedProducts" name="excluded_products[]" value="{{ $product->id }}">
                  <label class="form-check-label">{{ $product->name }}</label>
                </div>
              @endforeach
            </div>
          </div>

          <!-- Selección de categorías excluidas -->
          <div class="mb-3">
            <label class="form-label">Categorías Excluidas</label>
            <div id="editExcludedCategoriesList">
              @foreach($categories as $category)
                <div class="form-check">
                  <input type="checkbox" class="form-check-input editExcludedCategories" name="excluded_categories[]" value="{{ $category->id }}">
                  <label class="form-check-label">{{ $category->name }}</label>
                </div>
              @endforeach
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateCouponBtn" data-id="">Actualizar Cupón</button>
      </div>
    </div>
  </div>
</div>
