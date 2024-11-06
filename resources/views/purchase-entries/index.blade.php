@extends('layouts/layoutMaster')

@section('title', 'Listado de ingresos')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
  window.csrfToken = "{{ csrf_token() }}";
</script>
@vite(['resources/assets/js/app-purchase-entry-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Ingreso de materia prima /</span> Listado
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
  {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
  {{ $error }}
</div>
@endforeach
@endif

<!-- Purchase Entries Modal -->
<div class="card mb-4 purchase-entries">
  <div class="card-header">
    <h5 class="card-title mb-0">Entrada de Productos/Materias Primas</h5>
  </div>
  <div class="card-body">
    <div data-repeater-list="purchase_entries">
      @foreach ($purchaseOrderItems as $index => $purchaseOrderItem)
      @php
      $totalReceived = $purchaseEntriesSum[$purchaseOrderItem->id] ?? 0;
      $remainingQuantity = max($purchaseOrderItem->quantity - $totalReceived, 0);
      @endphp
      @if ($remainingQuantity > 0)
      <div data-repeater-item class="row mb-3">
        <div class="col-3">
          <label class="form-label" for="item">Producto/Materia Prima</label>
          <select class="form-select item-select" name="purchase_entries[{{ $index }}][purchase_order_items_id]" disabled >
            @if ($purchaseOrderItem->raw_material_id)
            <option value="{{ $purchaseOrderItem->id }}">{{ $purchaseOrderItem->rawMaterial->name }}</option>
            @else
            <option value="{{ $purchaseOrderItem->id }}">{{ $purchaseOrderItem->product->name }}</option>
            @endif
          </select>
        </div>

        <div class="col-2">
          <label class="form-label" for="total_quantity">Cantidad Total</label>
          <input type="number" class="form-control" name="purchase_entries[{{ $index }}][total_quantity]" value="{{ $purchaseOrderItem->quantity }}" disabled readonly>
        </div>

        <div class="col-2">
          <label class="form-label" for="remaining_quantity">Cantidad Restante</label>
          <input type="number" class="form-control" name="purchase_entries[{{ $index }}][remaining_quantity]" value="{{ $remainingQuantity }}" readonly>
        </div>

        <div class="col-2">
          <label class="form-label" for="received_quantity">Cantidad Recibida</label>
          <input type="number" class="form-control" name="purchase_entries[{{ $index }}][received_quantity]" placeholder="Cantidad Recibida" required>
        </div>

        <div class="col-3" style="width: 13%;">
          <label class="form-label" for="entry_date">Fecha de Entrada</label>
          <input type="date" class="form-control" name="purchase_entries[{{ $index }}][entry_date]" required>
        </div>

        <div class="col-1 d-flex align-items-end">
          <button class="btn bg-danger text-white bx bx-trash" data-repeater-delete=""></button>
        </div>
      </div>
      @endif
      @endforeach

    </div>
    <div class="text-end mt-3">
      <button type="button" id="submit-all-entries" class="btn btn-primary">Enviar todas las entradas</button>
    </div>
  </div>
</div>

@if($purchaseEntries->count() > 0 )
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Adjudicación de Lotes /</span> Loteado de entradas
</h4>
@endif
<!-- LISTADO DE LOTEADO -->
<div class="row row-cols-1 row-cols-md-2 g-4">
  @foreach ($purchaseEntries as $entry)
  <div class="col">
    <div class="card h-100 d-flex flex-row justify-content-between">
      <div class="card-body">
        <h5 class="card-title">
          Entrada: {{ $entry->item_name ?? 'N/A' }}
        </h5>
        <p class="card-text">ID Item de Orden: {{ $entry->purchase_order_items_id }}</p>
        <p class="card-text">Cantidad: {{ $entry->quantity }}</p>
        <p class="card-text">Fecha de Entrada: {{ \Carbon\Carbon::parse($entry->entry_date)->format('d-m-y') }}</p>
      </div>
      <div class="card-actions d-flex align-items-center justify-content-end">
        @if($entry->has_batches)
        <span class="badge bg-success">¡Lote(s) ingresados!</span>
        @else
        <button class="btn btn-primary open-lot-modal"
          data-entry-id="{{ $entry->id }}"
          data-entry-quantity="{{ $entry->quantity }}">
          Ingresar lote(s)
        </button>
        @endif
      </div>
    </div>
  </div>
  @endforeach
</div>




<div class="modal fade" id="batchModal" tabindex="-1" aria-labelledby="batchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="batchModalLabel">Ingresar Lotes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="batches-form">
          <div id="repeater">
            <div data-repeater-list="batches">
              <div data-repeater-item class="card mb-3">
                <div class="card-body row">
                  <div class="col-3">
                    <label for="batch_number" class="form-label">Número de Lote</label>
                    <input type="text" name="batch_number" class="form-control" required>
                  </div>
                  <div class="col-2">
                    <label for="quantity" class="form-label">Cantidad</label>
                    <input type="number" name="quantity" class="form-control lot-quantity" required>
                  </div>
                  <div class="col-3">
                    <label for="production_date" class="form-label">Fecha de Producción</label>
                    <input type="date" name="production_date" class="form-control" required>
                  </div>
                  <div class="col-3">
                    <label for="expiration_date" class="form-label">Fecha de Expiración</label>
                    <input type="date" name="expiration_date" class="form-control" required>
                  </div>
                  <div class="col-1 d-flex justify-content-end align-items-end">
                    <button type="button" data-repeater-delete class="btn btn-danger btn-icon">
                      <i class='bx bx-trash' style="color: white;"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <button type="button" data-repeater-create class="btn btn-success">Agregar Lote</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="save-lots">Guardar Lotes</button>
      </div>
    </div>
  </div>
</div>
@endsection

<style>
  .card {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-direction: row;
    padding: 20px;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  }

  .card-title {
    font-weight: bold;
    font-size: 1.2rem;
    margin-bottom: 8px;
  }

  .card-text {
    margin-bottom: 4px;
    font-size: 1rem;
    color: #6c757d;
  }

  .card-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-left: auto;
  }

  .text-end.mt-3 {
    display: flex;
    justify-content: flex-end;
    margin-right: 20px;
  }

  @media (max-width: 576px) {
    .text-end.mt-3 {
      flex-direction: column;
      align-items: center;
      margin-right: 0;
    }

    #submit-all-entries {
      width: 100%;
      margin-top: 15px;
    }

    .modal-body form {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
  }

  .btn-primary {
    background-color: #007bff;
    border: none;
    transition: background-color 0.2s ease;
  }

  .btn-primary:hover {
    background-color: #0056b3;
  }

  .badge.bg-success {
    padding: 8px 12px;
    font-size: 0.9rem;
  }
</style>