@extends('layouts/layoutMaster')

@section('title', 'Crear Presupuesto')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-budgets-add.js'])
@endsection

@section('content')

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
  {{ $error }}
</div>
@endforeach
@endif

<div class="app-ecommerce" data-products='@json($products)' data-allow-no-stock='{{ $companySettings->allow_BudgetsWhitoutStock ? "true" : "false" }}'>

  <!-- Add Budget -->
  <form action="{{ route('budgets.store') }}" method="POST" enctype="multipart/form-data" id="addBudgetForm">
    @csrf
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <!-- Título de la página y botones de acciones -->
      <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">Crear un nuevo presupuesto</h4>
      </div>
      <div class="d-flex align-content-center flex-wrap gap-3">
        <button type="button" class="btn btn-label-secondary" id="discardButton" data-url="{{ route('budgets.index') }}">Descartar</button>
        <button type="submit" name="action" value="publish" class="btn btn-primary" id="saveButton">Guardar</button>
      </div>
    </div>

    <!-- Primera fila -->
    <div class="row gx-3 mb-3">
      <!-- Columna 1 - Información del Cliente -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Información del Cliente</h5>
          </div>
          <div class="card-body">
            <!-- Tabs de Cliente/Lead -->
            <ul class="nav nav-tabs" id="clientTypeTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="client-tab" data-bs-toggle="tab" data-bs-target="#client-content"
                  type="button" role="tab" aria-controls="client-content" aria-selected="true">
                  Cliente
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="lead-tab" data-bs-toggle="tab" data-bs-target="#lead-content"
                  type="button" role="tab" aria-controls="lead-content" aria-selected="false">
                  Lead
                </button>
              </li>
            </ul>

            <div class="tab-content" id="clientTypeContent">
              <!-- Tab Cliente -->
              <div class="tab-pane fade show active" id="client-content" role="tabpanel" aria-labelledby="client-tab">
                <div class="mb-3 mt-3">
                  <label class="form-label" for="client_id">Seleccione un Cliente</label>
                  <select id="client_id" name="client_id" class="form-select select2">
                    <option value="">Seleccione un cliente</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <!-- Tab Lead -->
              <div class="tab-pane fade" id="lead-content" role="tabpanel" aria-labelledby="lead-tab">
                <div class="mb-3 mt-3">
                  <label class="form-label" for="lead_id">Seleccione un Lead</label>
                  <select id="lead_id" name="lead_id" class="form-select select2">
                    <option value="">Seleccione un lead</option>
                    @foreach($leads as $lead)
                    <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <!-- Campo para la lista de precios -->
            <div class="mb-3 mt-3">
              <label class="form-label" for="price_list_id">Lista de Precios</label>
              <select id="price_list_id" name="price_list_id" class="form-select select2" data-placeholder="Seleccione una lista de precios">
                <option value=""></option>
                @foreach($priceLists as $priceList)
                <option value="{{ $priceList->id }}">{{ $priceList->name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Campo para las notas -->
            <div class="mb-3">
              <label class="form-label" for="notes">Notas</label>
              <textarea id="notes" name="notes" class="form-control" placeholder="Agregue notas adicionales aquí..."></textarea>
            </div>

          </div>
        </div>
      </div>

      <!-- Columna 2 - Información General -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Información General</h5>
          </div>
          <div class="card-body">
            <!-- Campo para la tienda -->
            <div class="mb-3">
              <label class="form-label" for="store_id">Tienda <span class="text-danger">*</span></label>
              <select id="store_id" name="store_id" class="form-control select2" required>
                @foreach($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
              </select>
            </div>
            <!-- Campo para la fecha de vencimiento -->
            <div class="mb-3">
              <label class="form-label" for="due_date">Fecha de Vencimiento <span class="text-danger">*</span></label>
              <input type="date" id="due_date" name="due_date" class="form-control" required>
            </div>

            <!-- Campo para el tipo de descuento -->
            <div class="mb-3">
              <label class="form-label" for="discount_type">Tipo de Descuento</label>
              <select id="discount_type" name="discount_type" class="form-control select2">
                <option value="">Sin descuento</option>
                <option value="Percentage">Porcentaje</option>
                <option value="Fixed">Fijo</option>
              </select>
            </div>
            <!-- Campo para el descuento -->
            <div class="mb-3">
              <label class="form-label" for="discount">Descuento</label>
              <input type="number" id="discount" name="discount" class="form-control" step="0.01">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Segunda fila -->
    <div class="row gx-3">
      <!-- Columna 1 - Productos Seleccionados -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Productos Seleccionados</h5>
          </div>
          <div class="card-body">

            <!-- Campo para seleccionar productos -->
            <div class="mb-3">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="products">Productos <span class="text-danger">*</span></label>
              </div>
              <select class="select2 form-select" id="products" name="products[]" multiple="multiple" required>
                @foreach ($products as $product)
                <option value="{{ $product->id }}" data-build-price="{{ $product->build_price }}">
                  {{ $product->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div id="selectedProductsContainer" class="d-none">
              <!-- Tabla para productos seleccionados -->
              <table class="table" id="selectedProductsTable">
                <thead>
                  <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Stock</th>
                    <th>Precio unitario</th>
                    <th>Descuento Unitario</th>
                    <th>Costo total</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
              <!-- Alerta si algún producto no tiene build_price -->
              <div class="alert alert-danger d-none" id="priceAlert">
                Uno o más productos no tienen un precio asociado, no se puede calcular el costo total.
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Columna 2 - Resumen y Estado -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Resumen y Estado</h5>
          </div>
          <div class="card-body">

            <!-- Campo para el total -->
            <div class="mb-3">
              <label class="form-label" for="total">Total</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="text"
                  class="form-control bg-light"
                  id="total"
                  name="total"
                  readonly
                  disabled
                  value="0.00">
              </div>
            </div>
            <!-- Campo para el estado -->
            <div class="mb-3">
              <label class="form-label" for="status">Estado <span class="text-danger">*</span></label>
              <select id="status" name="status" class="form-control select2" required>
                <option value="draft">Borrador</option>
                <option value="pending_approval">Pendiente de Aprobación</option>
                <option value="sent">Enviado</option>
                <option value="negotiation">En Negociación</option>
                <option value="approved">Aprobado</option>
                <option value="rejected">Rechazado</option>
                <option value="expired">Expirado</option>
                <option value="cancelled">Cancelado</option>
              </select>
            </div>
            <!-- Campo para bloquear el presupuesto -->
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="is_blocked" name="is_blocked">
              <label class="form-check-label" for="is_blocked">Bloquear Presupuesto</label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<style>
  .select2 {
    width: 100% !important;
  }

  .select2-container {
    width: 100% !important;
  }

  .form-select,
  .form-control,
  .select2-container--default .select2-selection--single,
  .select2-container--default .select2-selection--multiple {
    width: 100% !important;
  }

  /* Ensure consistent heights */
  .select2-container .select2-selection--single,
  .select2-container .select2-selection--multiple {
    height: 38px !important;
    line-height: 38px !important;
  }

  /* Fix tab content padding */
  .tab-content {
    padding: 1rem 0 0 0; /* Remove bottom padding */
  }

  /* Ensure tab pane takes full width */
  .tab-pane {
    width: 100%;
  }

  /* Fix input group alignment */
  .input-group {
    display: flex;
    flex-wrap: nowrap;
    position: relative;
    width: 100%;
  }

  .input-group-text {
    display: flex;
    align-items: center;
    padding: 0.4375rem 0.875rem;
    font-size: 0.9375rem;
    font-weight: 400;
    line-height: 1.53;
    text-align: center;
    white-space: nowrap;
    background-color: #f5f5f9;
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
    border-right: 0;
    margin: 0;
  }

  .input-group > .form-control {
    position: relative;
    flex: 1 1 auto;
    width: 1%;
    min-width: 0;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  /* Remove any previous margin fixes */
  .input-group > :not(:first-child) {
    margin-left: -1px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .input-group > :not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  /* Adjust spacing for elements inside tab panes */
  .tab-pane .mb-3 {
    margin-bottom: 1rem !important;
  }

  /* Adjust spacing for elements after tab content */
  #clientTypeContent + .mb-3 {
    margin-top: 1rem !important;
  }
</style>
@endsection