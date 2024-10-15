<!-- Modal para ver los detalles de Cupón -->
<div class="modal fade" id="detailCouponModal" tabindex="-1" aria-labelledby="detailCouponModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailCouponModalLabel">Detalles del Cupón</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="detailCouponForm">
          <div class="mb-3">
            <label for="couponCode" class="form-label">Código</label>
            <input type="text" class="form-control" id="couponCode" name="couponCode" readonly>
          </div>
          <div class="mb-3">
            <label for="couponType" class="form-label">Tipo</label>
            <select class="form-select" id="couponType" name="couponType" disabled>
              <option value="fixed">Fijo</option>
              <option value="percentage">Porcentaje</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="couponAmount" class="form-label">Valor</label>
            <input type="number" class="form-control" id="couponAmount" name="couponAmount" readonly>
          </div>
          <div class="mb-3">
            <label for="couponExpiry" class="form-label">Fecha de Expiración</label>
            <input type="date" class="form-control" id="couponExpiry" name="couponExpiry" readonly>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
