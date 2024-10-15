<!-- Modal Edit Expense -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editExpenseModalLabel">Editar Gasto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editExpenseForm" action="">
          <div class="mb-3">
            <label for="amountEdit" class="form-label">Monto</label>
            <input type="number" class="form-control" id="amountEdit" name="amount" required placeholder="Ingrese el monto del gasto">
          </div>
          {{-- <div class="mb-3">
            <label for="statusEdit" class="form-label">Estado</label>
            <select class="form-select" id="statusEdit" name="status" required>
              <option value="" disabled selected>Seleccione un estado</option>
              @foreach($expenseStatus as $value => $name)
                  <option value="{{ $value }}">{{ $name }}</option>
              @endforeach
            </select>
          </div> --}}
          <div class="mb-3">
            <label for="dueDateEdit" class="form-label">Fecha de Vencimiento</label>
            <input type="date" class="form-control" id="dueDateEdit" name="due_date" required>
          </div>
          <div class="mb-3">
            <label for="supplierIdEdit" class="form-label">Proveedor</label>
            <select class="form-select" id="supplierIdEdit" name="supplier_id" required>
              <option value="" disabled selected>Seleccione un proveedor</option>
              @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="expenseCategoryIdEdit" class="form-label">Categoría de Gasto</label>
            <select class="form-select" id="expenseCategoryIdEdit" name="expense_category_id" required>
              <option value="" disabled selected>Seleccione una categoría</option>
              @foreach($expenseCategories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="expenseCurrencyIdEdit" class="form-label">Moneda</label>
            <select class="form-select" id="expenseCurrencyIdEdit" name="expenseCurrencyIdEdit" required>
              <option value="" selected disabled>Seleccione una moneda</option>
              @foreach($currencies as $currency)
                <option value="{{ $currency->id }}">{{ $currency->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="storeIdEdit" class="form-label">Empresa</label>
            <select class="form-select" id="storeIdEdit" name="store_id" required>
              <option value="" disabled selected>Seleccione una empresa</option>
              @foreach($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateExpenseBtn">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
