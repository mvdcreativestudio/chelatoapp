<!-- Modal Add New Coupon -->
<div class="modal fade" id="addCouponModal" tabindex="-1" aria-labelledby="addCouponModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCouponModalLabel">Agregar Nuevo Cupón</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewCouponForm">
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
          <!-- Uso único -->
          <div class="mb-3 form-check">
            <input type="hidden" name="single_use" value="0">
            <input type="checkbox" class="form-check-input" id="singleUse" name="single_use" value="1">
            <label class="form-check-label" for="singleUse">Este cupón es de uso único por cliente</label>
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
            <div id="excludedProductsList">
              @foreach($products as $product)
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="excluded_products[]" value="{{ $product->id }}">
                  <label class="form-check-label">{{ $product->name }}</label>
                </div>
              @endforeach
            </div>
          </div>

          <!-- Selección de categorías excluidas -->
          <div class="mb-3">
            <label class="form-label">Categorías Excluidas</label>
            <div id="excludedCategoriesList">
              @foreach($categories as $category)
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="excluded_categories[]" value="{{ $category->id }}">
                  <label class="form-check-label">{{ $category->name }}</label>
                </div>
              @endforeach
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitCouponBtn" data-route="{{ route('coupons.store') }}">Guardar Cupón</button>
      </div>
    </div>
  </div>
</div>
