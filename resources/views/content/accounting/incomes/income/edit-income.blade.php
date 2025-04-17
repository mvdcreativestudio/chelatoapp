<!-- Modal Edit Income -->
<div class="modal fade" id="updateIncomeModal" tabindex="-1" aria-labelledby="updateIncomeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateIncomeModalLabel">Editar Venta Libre</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editIncomeForm">
          <!-- Nombre Venta Libre -->
          <div class="mb-3">
            <label for="edit_income_name" class="form-label">Nombre *</label>
            <input type="text" class="form-control" id="edit_income_name" name="income_name" required
              placeholder="Ingrese el nombre de la venta">
          </div>

          <!-- Descripción Venta Libre -->
          <div class="mb-3">
            <label for="edit_income_description" class="form-label">Descripción</label>
            <textarea class="form-control" id="edit_income_description" name="income_description"
              placeholder="Ingrese una descripción para la venta"></textarea>
          </div>

          <!-- Fecha Venta Libre -->
          <div class="mb-3">
            <label for="edit_income_date" class="form-label">Fecha *</label>
            <input type="date" class="form-control" id="edit_income_date" name="income_date" required>
          </div>

          <!-- Método de Pago -->
          <div class="mb-3">
            <label for="edit_payment_method_id" class="form-label">Método de Pago *</label>
            <select class="form-select" id="edit_payment_method_id" name="payment_method_id" required>
              <option value="" selected disabled>Seleccione un método de pago</option>
              @foreach($paymentMethods as $method)
              <option value="{{ $method->id }}">{{ $method->description }}</option>
              @endforeach
            </select>
          </div>

          <!-- Categoría Venta Libre (nullable) -->
          <div class="mb-3">
            <label class="form-label mb-1 d-flex justify-content-between align-items-center" for="edit_category-org">
                <span>Categoría</span>
                <a href="#" class="fw-medium" onclick="redirectToCategories()">
                    Ir a Crear categoría
                </a>
            </label>
            <select class="form-select" id="edit_income_category_id" name="income_category_id">
              <option value="" selected>Sin Categoría</option>
              @foreach($incomeCategories as $category)
              <option value="{{ $category->id }}">{{ $category->income_name }}</option>
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
              @if($client->company_name)
              <option value="{{ $client->id }}">{{ $client->company_name }}</option>
              @else
              <option value="{{ $client->id }}">{{ $client->name }} {{ $client->lastname }}</option>
              @endif
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

          <!-- Moneda -->
          <div class="mb-3">
              <label for="edit_currency" class="form-label">Moneda *</label>
              <select class="form-select" id="edit_currency" name="currency" required>
                  <option value="">Seleccione una moneda</option>
                  <option value="Peso">Peso</option>
                  <option value="Dólar">Dólar</option>
              </select>
          </div>

          <!-- Campo de Cotización (inicialmente oculto) -->
          <div class="mb-3" id="edit_exchange_rate_field" style="display: none;">
            <label for="edit_exchange_rate" class="form-label">Cotización *</label>
            <input type="number" class="form-control" id="edit_exchange_rate" name="exchange_rate" step="0.01" value="0">
          </div>

          <!-- Tasa de Impuesto -->
          <div class="mb-3">
            <label for="edit_tax_rate_id" class="form-label">Tasa de Impuesto</label>
            <select class="form-select" id="edit_tax_rate_id" name="tax_rate_id">
              <option value="" selected>Sin Impuesto</option>
              @foreach($taxes as $tax)
              <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
              @endforeach
            </select>
          </div>

          <!-- Sección de Items -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Items *</label>
                <button type="button" class="btn btn-primary btn-sm" id="editAddItemBtn">
                    <i class="fas fa-plus"></i> Agregar Item
                </button>
            </div>
            <table class="table table-bordered" id="editItemsTable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th width="60">Acción</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitEditIncomeBtn" data-route="{{ route('incomes.update', ':id') }}">
          Guardar Cambios
        </button>
      </div>
    </div>
  </div>
</div>