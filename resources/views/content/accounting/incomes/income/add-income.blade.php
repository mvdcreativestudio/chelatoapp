<!-- Modal Add New Income -->
<div class="modal fade" id="addIncomeModal" tabindex="-1" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addIncomeModalLabel">Agregar Nuevo Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewIncomeForm">
          <!-- Nombre del Ingreso -->
          <div class="mb-3">
            <label for="income_name" class="form-label">Nombre del Ingreso</label>
            <input type="text" class="form-control" id="income_name" name="income_name" required placeholder="Ingrese el nombre del ingreso">
          </div>

          <!-- Descripción del Ingreso -->
          <div class="mb-3">
            <label for="income_description" class="form-label">Descripción del Ingreso</label>
            <textarea class="form-control" id="income_description" name="income_description" placeholder="Ingrese una descripción del ingreso (opcional)"></textarea>
          </div>

          <!-- Fecha del Ingreso -->
          <div class="mb-3">
            <label for="income_date" class="form-label">Fecha del Ingreso</label>
            <input type="date" class="form-control" id="income_date" name="income_date" required value="{{ date('Y-m-d') }}">
          </div>

          <!-- Importe del Ingreso -->
          <div class="row mb-3">
            <div class="col-4">
              <label for="currency_id" class="form-label">Moneda</label>
              <select class="form-select" id="currency_id" name="currency_id" required>
                <option value="" selected disabled>Seleccione</option>
                @foreach($currencies as $currency)
                <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-8">
              <label for="income_amount" class="form-label">Importe</label>
              <input type="number" class="form-control" id="income_amount" name="income_amount" required placeholder="Ingrese el importe del ingreso">
            </div>
          </div>

          <!-- Método de Pago -->
          <div class="mb-3">
            <label for="payment_method_id" class="form-label">Método de Pago</label>
            <select class="form-select" id="payment_method_id" name="payment_method_id" required>
              <option value="" selected disabled>Seleccione un método de pago</option>
              @foreach($paymentMethods as $method)
              <option value="{{ $method->id }}">{{ $method->description }}</option>
              @endforeach
            </select>
          </div>

          <!-- Categoría del Ingreso -->
          <div class="mb-3">
            <label class="form-label mb-1 d-flex justify-content-between align-items-center" for="income_category_id">
              <span>Categoría del Ingreso</span><a href="{{ route('income-categories.index') }}" class="fw-medium">Ir a Crear categoría</a>
            </label>
            <select class="form-select" id="income_category_id" name="income_category_id">
              <option value="" selected disabled>Seleccione una categoría</option>
              @foreach($incomeCategories as $category)
              <option value="{{ $category->id }}">{{ $category->income_name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Tipo de Entidad -->
          <div class="mb-3">
            <label for="entity_type" class="form-label">Tipo de Entidad</label>
            <select class="form-select" id="entity_type" name="entity_type" required>
              <option value="none" selected>Ninguno</option>
              <option value="client">Cliente</option>
              <option value="supplier">Proveedor</option>
            </select>
          </div>

          <!-- Cliente (se oculta inicialmente) -->
          <div class="mb-3" id="client_field" style="display: none;">
            <label class="form-label mb-1 d-flex justify-content-between align-items-center" for="client_id">
              <span>Cliente</span><a href="{{ route('clients.index') }}" class="fw-medium">Ir a Crear cliente</a>
            </label>
            <select class="form-select" id="client_id" name="client_id">
              <option value="" selected disabled>Seleccione un cliente</option>
              @foreach($clients as $client)
              <option value="{{ $client->id }}">{{ $client->name }} {{ $client->lastname }}</option>
              @endforeach
            </select>
          </div>

          <!-- Proveedor (se oculta inicialmente) -->
          <div class="mb-3" id="supplier_field" style="display: none;">
            <label class="form-label mb-1 d-flex justify-content-between align-items-center" for="supplier_id">
              <span>Proveedor</span><a href="{{ route('suppliers.create') }}" class="fw-medium">Ir a Crear proveedor</a>
            </label>
            <select class="form-select" id="supplier_id" name="supplier_id">
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
        <button type="button" class="btn btn-primary" id="submitIncomeBtn" data-route="{{ route('incomes.store') }}">Guardar Ingreso</button>
      </div>
    </div>
  </div>
</div>