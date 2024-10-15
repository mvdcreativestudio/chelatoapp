<!-- Modal Add New Expense Payment Method -->
<div class="modal fade" id="addExpensePaymentMethodModal" tabindex="-1" aria-labelledby="addExpensePaymentMethodModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addExpensePaymentMethodModalLabel">Agregar Nuevo Detalle de Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewExpensePaymentMethodForm">
          <input type="hidden" name="expense_id" id="expense_id" value="{{ $expense->id }}">
          <div class="mb-3">
            <label for="amount_paid" class="form-label">Monto a Abonar</label>
            <input type="number" class="form-control" id="amount_paid" name="amount_paid" required placeholder="Ingrese el Monto a Abonar">
          </div>
          <div class="mb-3">
            <label for="payment_date" class="form-label">Fecha de Pago</label>
            <input type="date" class="form-control" id="payment_date" name="payment_date" required>
          </div>
          <div class="mb-3">
            <label for="payment_method_id" class="form-label">Método de Pago</label>
            <select class="form-select" id="payment_method_id" name="payment_method_id" required>
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
        <button type="button" class="btn btn-primary" id="submitExpensePaymentMethodBtn"
          data-route="{{ route('expense-payment-methods.store') }}">Guardar Pago</button>
      </div>
    </div>
  </div>
</div>