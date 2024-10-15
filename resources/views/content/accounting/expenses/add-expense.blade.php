<!-- Modal Add New Expense -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addExpenseModalLabel">Agregar Nuevo Gasto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewExpenseForm">
          <div class="mb-3">
            <label for="amount" class="form-label">Monto</label>
            <input type="number" class="form-control" id="amount" name="amount" required placeholder="Ingrese el monto del gasto">
          </div>
          <div class="mb-3">
            <label for="due_date" class="form-label">Fecha de Vencimiento</label>
            <input type="date" class="form-control" id="due_date" name="due_date" required value="{{ date('Y-m-d') }}">
          </div>
          <div class="mb-3">
            <label for="supplier_id" class="form-label">Proveedor</label>
            <select class="form-select" id="supplier_id" name="supplier_id" required>
              <option value="" selected disabled>Seleccione un proveedor</option>
              @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="expense_category_id" class="form-label">Categoría de Gasto</label>
            <select class="form-select" id="expense_category_id" name="expense_category_id" required>
              <option value="" selected disabled>Seleccione una categoría</option>
              @foreach($expenseCategories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="currency_id" class="form-label">Moneda</label>
            <select class="form-select" id="currency_id" name="currency_id" required>
              <option value="" selected disabled>Seleccione una moneda</option>
              @foreach($currencies as $currency)
                <option value="{{ $currency->id }}">{{ $currency->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="store_id" class="form-label">Empresa</label>
            <select class="form-select" id="store_id" name="store_id" required>
              <option value="" selected>Ninguna</option>
              @foreach($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- checbox ¿Esta Pago? --}}
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_paid" name="is_paid">
            <label class="form-check-label" for="is_paid">¿Está Pagado?</label>
          </div>
          <div id="paymentFields" class="d-none">
            <div class="mb-3">
              <label for="amount_paid" class="form-label">Monto Pagado</label>
              <input type="number" class="form-control" id="amount_paid" name="amount_paid" placeholder="Ingrese el monto pagado">
            </div>
            <div class="mb-3">
              <label for="payment_method_id" class="form-label">Método de Pago</label>
              <select class="form-select" id="payment_method_id" name="payment_method_id">
                <option value="" selected disabled>Seleccione un método de pago</option>
                @foreach($paymentMethods as $method)
                  <option value="{{ $method->id }}">{{ $method->description }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitExpenseBtn"
          data-route="{{ route('expenses.store') }}">Guardar Gasto</button>
      </div>
    </div>
  </div>
</div>
