@extends('layouts/layoutMaster')

@section('title', 'Detalle del Presupuesto')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-budgets-detail.js'])
@parent
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Presupuestos /</span> Detalle del Presupuesto
</h4>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 card p-3">
  <div class="d-flex flex-column justify-content-center">
    <h6 class="mb-1 mt-3">Presupuesto #{{ $budget->id }}
      <span class="badge bg-label-{{ 
          $budget->status()->latest()->first()->status === 'draft' ? 'warning' : 
          ($budget->status()->latest()->first()->status === 'pending_approval' ? 'info' : 
          ($budget->status()->latest()->first()->status === 'sent' ? 'primary' : 
          ($budget->status()->latest()->first()->status === 'negotiation' ? 'info' : 
          ($budget->status()->latest()->first()->status === 'approved' ? 'success' : 
          ($budget->status()->latest()->first()->status === 'rejected' ? 'danger' : 
          ($budget->status()->latest()->first()->status === 'expired' ? 'secondary' : 
          ($budget->status()->latest()->first()->status === 'cancelled' ? 'danger' : 'info'))))))) 
      }} me-2 ms-2">
        {{ $statusTranslations[$budget->status()->latest()->first()->status] ?? ucfirst($budget->status()->latest()->first()->status) }}
      </span>
      @if($budget->is_blocked)
      <span class="badge bg-label-danger">Bloqueado</span>
      @endif
    </h6>

    <h6 class="card-title mb-1 mt-1">Cliente:
      <span class="mb-1 me-2 ms-2">
        {{ $budget->client ? $budget->client->name : ($budget->lead ? $budget->lead->name : 'Sin cliente') }}
      </span>
    </h6>

    <h6 class="card-title mb-1 mt-1">Tienda:
      <span class="mb-1 me-2 ms-2">{{ $budget->store->name }}</span>
    </h6>

    <p class="text-body mb-1">Fecha de vencimiento: {{ date('d/m/Y', strtotime($budget->due_date)) }}</p>
  </div>

  <div class="d-flex align-content-center flex-wrap gap-2">
    <a href="{{ route('budgets.edit', $budget->id) }}" class="btn btn-sm btn-info">
      <i class="bx bx-pencil"></i> Editar
    </a>
    <button type="button"
      class="btn btn-sm btn-success convert-to-order"
      data-id="{{ $budget->id }}"
      data-url="{{ route('budgets.convertToOrder', $budget->id) }}">
      <i class="bx bx-cart"></i> Generar Venta
    </button>
    <button type="button"
      class="btn btn-sm btn-secondary notify-by-email"
      data-id="{{ $budget->id }}"
      data-url="{{ route('budgets.sendEmail') }}">
      <i class="bx bx-envelope"></i> Notificar por Email
    </button>

    <a href="{{ route('budgets.pdf', ['budget' => $budget->id]) }}?action=print" target="_blank"
      onclick="window.open(this.href, 'print_window', 'left=100,top=100,width=800,height=600').print(); return false;">
      <button class="btn btn-sm btn-primary">
        <i class="bx bx-printer"></i> Imprimir
      </button>
    </a>



    <a href="{{ route('budgets.pdf', $budget->id) }}" class="btn btn-sm btn-label-primary">
      <i class="bx bxs-file-pdf"></i> Exportar PDF
    </a>
    @if(!$budget->is_blocked)
    <button type="button" class="btn btn-sm btn-danger delete-budget"
      data-id="{{ $budget->id }}"
      data-bs-toggle="tooltip"
      data-bs-placement="top"
      title="Eliminar presupuesto">
      <i class="bx bx-trash"></i>
    </button>
    @endif
  </div>
</div>



<!-- Budget Details Table -->
<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Detalles del Presupuesto</h5>
      </div>
      <div class="card-datatable table-responsive">
        <table class="datatables-budget-details table">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Stock</th>
              <th>Precio unitario</th>
              <th>Descuento</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($budget->items as $item)
            <tr>
              <td>{{ $item->product->name }}</td>
              <td>{{ $item->quantity }}</td>
              <td>{{ $item->product->stock }}</td>
              <td>${{ number_format($item->price, 2) }}</td>
              <td>
                @if($item->discount_type === 'Percentage')
                {{ $item->discount_price }}%
                @elseif($item->discount_type === 'Fixed')
                ${{ number_format($item->discount_price, 2) }}
                @else
                -
                @endif
              </td>
              <td>${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>

        <div class="d-flex justify-content-end align-items-center m-3 mb-2 p-1">
          <div class="order-calculations">
            <div class="d-flex justify-content-between mb-2">
              <span class="w-px-100">Subtotal:</span>
              <span class="text-heading">${{ number_format($budget->subtotal, 2) }}</span>
            </div>
            @if($budget->discount)
            <div class="d-flex justify-content-between mb-2">
              <span class="w-px-100">Descuento:</span>
              <span class="text-heading">
                @if($budget->discount_type === 'Percentage')
                {{ $budget->discount }}%
                @else
                ${{ number_format($budget->discount, 2) }}
                @endif
              </span>
            </div>
            @endif
            <div class="d-flex justify-content-between mb-2 pt-2 border-top">
              <h6 class="mb-0">Total:</h6>
              <h6 class="mb-0">${{ number_format($budget->total, 2) }}</h6>
            </div>
          </div>
        </div>

        @if($budget->notes)
        <div class="order-notes mt-3 p-3 rounded bg-light print-visible">
          <h6 class="mb-2"><i class="bx bx-note"></i> Notas:</h6>
          <p class="text-muted mb-0">{{ $budget->notes }}</p>
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">Estado del Presupuesto</h5>
      </div>
      <div class="card-body">
        <form id="updateStatusForm" data-budget-id="{{ $budget->id }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Estado actual</label>
            <select class="form-select" name="status" id="status">
              @foreach($statusTranslations as $value => $label)
              <option value="{{ $value }}" {{ $budget->status()->latest()->first()->status === $value ? 'selected' : '' }}>
                {{ $label }}
              </option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary btn-sm">Actualizar Estado</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendEmailModalLabel">Enviar Presupuesto por Correo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="sendEmailForm" method="POST" action="{{ route('budgets.sendEmail') }}">
        @csrf
        <input type="hidden" name="budget_id" value="{{ $budget->id }}">
        <div class="modal-body">
          <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico del cliente</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="{{ $budget->client->email ?? $budget->lead->email ?? '' }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Enviar</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection