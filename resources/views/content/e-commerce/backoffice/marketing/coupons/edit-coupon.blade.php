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
            <div class="input-group">
              <span class="input-group-text">UYU</span>
              <input type="number" class="form-control" id="couponAmount" name="couponAmount" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="couponExpiry" class="form-label">Fecha de Expiración</label>
            <input type="date" class="form-control" id="couponExpiry" name="couponExpiry">
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
