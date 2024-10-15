@extends('layouts/layoutMaster')

@section('title', 'Detalle de Gasto')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'
])
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
<script>
  window.baseUrl = "{{ url('/') }}";
  window.expenseId = "{{ $expense->id }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/expenses/expenses-payments-methods/app-expenses-payments-methods-list.js',
'resources/assets/js/expenses/expenses-payments-methods/app-expenses-payments-methods-add.js',
'resources/assets/js/expenses/expenses-payments-methods/app-expenses-payments-methods-edit.js',
'resources/assets/js/expenses/expenses-payments-methods/app-expenses-payments-methods-delete.js',
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Detalle de Gasto
</h4>

@if (Auth::user()->can('access_datacenter'))
<div class="card mb-4">
  <div class="card-body card-widget-separator">
    <div class="row gy-4 gy-sm-1">
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Total del Gasto</h6>
            <h4 class="mb-2">{{ $settings->currency_symbol }} {{ number_format($expense->amount, 2) }}</h4>
          </div>
          <div class="avatar me-sm-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-receipt bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Total del Gasto Abonado</h6>
            <h4 class="mb-2">{{ $settings->currency_symbol }} {{ number_format($expense->totalPayments, 2) }}</h4>
          </div>
          <div class="avatar me-lg-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-dollar bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="mb-2">Total del Gasto Pendiente</h6>
            <h4 class="mb-2">{{ $settings->currency_symbol }} {{ number_format($expense->differenceAmountPaid, 2) }}</h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-time bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      {{-- volver atras --}}
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="mb-2">Volver Atrás</h6>
            <a href="{{ route('expenses.index') }}" class="btn btn-primary">Volver</a>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-arrow-back bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      {{-- <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="mb-2">Gastos Pagados</h6>
            <h4 class="mb-2">{{ $paidExpenses }}</h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-check bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start border-end pb-3 pb-sm-0 card-widget-3">
          <div>
            <h6 class="mb-2">Gastos Parcialmente Pagados</h6>
            <h4 class="mb-2">{{ $partialExpenses }}</h4>
          </div>
          <div class="avatar me-sm-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-hourglass bx-sm"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
          <div>
            <h6 class="mb-2">Gastos No pagados</h6>
            <h4 class="mb-2">{{ $unpaidExpenses }}</h4>
          </div>
          <div class="avatar me-sm-4">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-time bx-sm"></i>
            </span>
          </div>
        </div>
      </div> --}}
    </div>
  </div>
</div>
@endif

<!-- Expenses List Table -->
<div class="card">
  <div class="card pb-3">
    <h5 class="card-header pb-0">
      Detalle de Gasto
      <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addExpensePaymentMethodModal">
        Agregar Detalle del Gasto
      </button>
      <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches"
            aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>
      <div class="collapse" id="columnSwitches">
        <div class="mt-0 d-flex flex-wrap">
          <!-- Selectores de columnas -->
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Monto</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Fecha</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Metodo Pago</span>
            </label>
          </div>
          <div class="mx-3">
            <label class="switch switch-square">
              <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
              <span class="switch-toggle-slider">
                <span class="switch-on"><i class="bx bx-check"></i></span>
                <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
              <span class="switch-label">Acciones</span>
            </label>
          </div>
        </div>
        <div class="dropdown d-inline float-end mx-2">
          <button class="btn btn-primary dropdown-toggle d-none" type="button" id="dropdownMenuButton"
            data-bs-toggle="dropdown" aria-expanded="false">
            Acciones
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="#" id="deleteSelected">Eliminar seleccionados</a></li>
          </ul>
        </div>
      </div>
      <!-- Filter for expenses -->
      <div class="d-flex justify-content-start align-items-center row py-3 gap-3 mb-0 pb-0 gap-md-0">
        <div class="col-md-2 payment_method">
          <label for="payment_method">Metodo de Pago</label>
        </div>
        {{-- filter for date --}}
        <div class="col-md-2">
          <label for="startDate">Fecha Desde</label>
          <input type="date" class="form-control date-range-filter" id="startDate" placeholder="Fecha de inicio">
        </div>
        <div class="col-md-2">
          <label for="endDate">Fecha Hasta</label>
          <input type="date" class="form-control date-range-filter" id="endDate" placeholder="Fecha de fin">
        </div>
        {{-- button for clear filters --}}
        <div class="col-md-2 mt-2">
          <button class="btn btn-outline-danger btn-sm clear-filters" id="clear-filters">
            <i class="fas fa-eraser"></i> Limpiar Filtros
          </button>
        </div>
      </div>
    </h5>
  </div>

  <div class="card-datatable table-responsive pt-0">
    @if($expensePaymentMethod->count() > 0)
    <table class="table datatables-expenses-payments-methods" data-symbol="{{ $settings->currency_symbol }}">
      <thead>
        <tr>
          <th>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="checkAll">
            </div>
          </th>
          <th>N°</th>
          <th>Monto</th>
          <th>Fecha</th>
          <th>Metodo Pago</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <!-- Datos llenados por DataTables -->
      </tbody>
    </table>
    @else
    <div class="text-center py-5">
      <h4>No hay detalles del gasto</h4>
      <p class="text-muted">Agrega un nuevo detalle de gasto</p>
    </div>
    @endif
  </div>
</div>


@include('content.accounting.expenses.expenses-payments-methods.add-expense-payment-method')
@include('content.accounting.expenses.expenses-payments-methods.edit-expense-payment-method')
{{-- @include('content.accounting.expenses.details-expense') --}}

@endsection