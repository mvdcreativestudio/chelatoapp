<!-- Modal Edit New Expense Payment Method -->
<div class="modal fade" id="editExpensePaymentMethodModal" tabindex="-1" aria-labelledby="editExpensePaymentMethodModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editExpensePaymentMethodModalLabel">Editar Detalle de Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editExpensePaymentMethodForm">
          <input type="hidden" name="expense_id_edit" id="expense_id_edit" value="{{ $expense->id }}">
          <div class="mb-3">
            <label for="amount_paid_edit" class="form-label">Monto a Abonar</label>
            <input type="number" class="form-control" id="amount_paid_edit" name="amount_paid_edit" required placeholder="Ingrese el Monto a Abonar">
          </div>
          <div class="mb-3">
            <label for="payment_date_edit" class="form-label">Fecha de Pago</label>
            <input type="date" class="form-control" id="payment_date_edit" name="payment_date_edit" required>
          </div>
          <div class="mb-3">
            <label for="payment_method_id_edit" class="form-label">Método de Pago</label>
            <select class="form-select" id="payment_method_id_edit" name="payment_method_id_edit" required>
              <option value="" selected disabled>Seleccione un método de pago</option>
              @foreach ($paymentsMethods as $paymentMethod)
                <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->description }}</option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateExpensePaymentMethodBtn">Editar Detalle Pago</button>
      </div>
    </div>
  </div>
</div>