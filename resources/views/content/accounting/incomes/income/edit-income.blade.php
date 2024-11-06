<!-- Modal Edit Income -->
<div class="modal fade" id="editIncomeModal" tabindex="-1" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editIncomeModalLabel">Editar Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editIncomeForm">
          <!-- Nombre del Ingreso -->
          <div class="mb-3">
            <label for="edit_income_name" class="form-label">Nombre del Ingreso</label>
            <input type="text" class="form-control" id="edit_income_name" name="income_name" required>
          </div>

          <!-- Descripción del Ingreso -->
          <div class="mb-3">
            <label for="edit_income_description" class="form-label">Descripción del Ingreso</label>
            <textarea class="form-control" id="edit_income_description" name="income_description"></textarea>
          </div>

          <!-- Fecha del Ingreso -->
          <div class="mb-3">
            <label for="edit_income_date" class="form-label">Fecha del Ingreso</label>
            <input type="date" class="form-control" id="edit_income_date" name="income_date" required>
          </div>

          <!-- Importe del Ingreso -->
          <div class="mb-3">
            <label for="edit_income_amount" class="form-label">Importe</label>
            <input type="number" class="form-control" id="edit_income_amount" name="income_amount" required>
          </div>

          <!-- Método de Pago -->
          <div class="mb-3">
            <label for="edit_payment_method_id" class="form-label">Método de Pago</label>
            <select class="form-select" id="edit_payment_method_id" name="payment_method_id" required>
              <option value="" selected disabled>Seleccione un método de pago</option>
              @foreach($paymentMethods as $method)
                <option value="{{ $method->id }}">{{ $method->description }}</option>
              @endforeach
            </select>
          </div>

          <!-- Categoría del Ingreso -->
          <div class="mb-3">
            <label for="edit_income_category_id" class="form-label">Categoría del Ingreso</label>
            <select class="form-select" id="edit_income_category_id" name="income_category_id" required>
              <option value="" selected disabled>Seleccione una categoría</option>
              @foreach($incomeCategories as $category)
                <option value="{{ $category->id }}">{{ $category->income_name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Moneda del Ingreso -->
          <div class="mb-3">
            <label for="edit_currency_id" class="form-label">Moneda</label>
            <select class="form-select" id="edit_currency_id" name="currency_id" required>
              <option value="" selected disabled>Seleccione una moneda</option>
              @foreach($currencies as $currency)
                <option value="{{ $currency->id }}">{{ $currency->name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Tipo de Entidad -->
          <div class="mb-3">
            <label for="edit_entity_type" class="form-label">Tipo de Entidad</label>
            <select class="form-select" id="edit_entity_type" name="entity_type" required>
              <option value="none" selected>Ninguno</option>
              <option value="client">Cliente</option>
              <option value="supplier">Proveedor</option>
            </select>
          </div>

          <!-- Cliente (se oculta inicialmente) -->
          <div class="mb-3" id="edit_client_field" style="display: none;">
            <label for="edit_client_id" class="form-label">Cliente</label>
            <select class="form-select" id="edit_client_id" name="client_id">
              <option value="" selected disabled>Seleccione un cliente</option>
              @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Proveedor (se oculta inicialmente) -->
          <div class="mb-3" id="edit_supplier_field" style="display: none;">
            <label for="edit_supplier_id" class="form-label">Proveedor</label>
            <select class="form-select" id="edit_supplier_id" name="supplier_id">
              <option value="" selected disabled>Seleccione un proveedor</option>
              @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitEditIncomeBtn" data-route="{{ route('incomes.update', ':id') }}">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
